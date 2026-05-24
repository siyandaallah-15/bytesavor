<?php
// ============================================================
//  ByteSavor — api/shifts.php
//
//  POST action=clock_in          → staff clocks in
//  POST action=clock_out         → staff clocks out
//  GET  ?action=status           → is current user clocked in?
//  GET  ?action=history&id=X     → shift history for a staff member
//  GET  ?action=on_shift         → who is currently on shift
//  GET  ?action=payroll          → payroll summary for a period
//  POST action=set_pay           → manager sets pay type + rate
// ============================================================

require_once __DIR__ . '/auth.php';

$db     = getRestaurantDB($restaurantId);
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user   = me();

switch ($action) {

    // ════════════════════════════════════════
    //  POST: Clock in
    //  Staff member starts their shift
    // ════════════════════════════════════════
    case 'clock_in':
        requireMethod('POST');

        // Check if already clocked in today
        $check = $db->prepare("
            SELECT id FROM shift_log
            WHERE user_id = ?
              AND restaurant_id = ?
              AND clock_out IS NULL
              AND DATE(clock_in) = CURDATE()
            LIMIT 1
        ");
        $check->execute([$user['id'], $restaurantId]);
        if ($check->fetch()) {
            fail('You are already clocked in. Please clock out first.');
        }

        $db->prepare("
            INSERT INTO shift_log (user_id, restaurant_id, clock_in)
            VALUES (?, ?, NOW())
        ")->execute([$user['id'], $restaurantId]);

        auditLog($db, $restaurantId, 'login',
            "{$user['name']} ({$user['staff_id']}) clocked in"
        );

        ok(['clocked_in' => true, 'time' => date('H:i')], 'Clocked in successfully.');
        break;

    // ════════════════════════════════════════
    //  POST: Clock out
    //  Staff member ends their shift
    // ════════════════════════════════════════
    case 'clock_out':
        requireMethod('POST');

        // Find the open shift for today
        $shift = $db->prepare("
            SELECT id, clock_in FROM shift_log
            WHERE user_id = ?
              AND restaurant_id = ?
              AND clock_out IS NULL
            ORDER BY clock_in DESC LIMIT 1
        ");
        $shift->execute([$user['id'], $restaurantId]);
        $shift = $shift->fetch();

        if (!$shift) {
            fail('You are not currently clocked in.');
        }

        $db->prepare("
            UPDATE shift_log SET clock_out = NOW()
            WHERE id = ?
        ")->execute([$shift['id']]);

        // Calculate hours for this shift
        $hours = $db->prepare("
            SELECT ROUND(TIMESTAMPDIFF(MINUTE, clock_in, NOW()) / 60, 2) AS hours
            FROM shift_log WHERE id = ?
        ");
        $hours->execute([$shift['id']]);
        $hoursWorked = $hours->fetch()['hours'];

        auditLog($db, $restaurantId, 'logout',
            "{$user['name']} ({$user['staff_id']}) clocked out — {$hoursWorked}h worked"
        );

        ok([
            'clocked_in'   => false,
            'hours_worked' => $hoursWorked,
            'time'         => date('H:i'),
        ], "Clocked out. You worked {$hoursWorked} hours today.");
        break;

    // ════════════════════════════════════════
    //  GET: Check if current user is clocked in
    //  Called by dashboard on load to show
    //  correct button state
    // ════════════════════════════════════════
    case 'status':
        requireMethod('GET');

        $shift = $db->prepare("
            SELECT id, clock_in,
                   ROUND(TIMESTAMPDIFF(MINUTE, clock_in, NOW()) / 60, 2) AS hours_so_far
            FROM shift_log
            WHERE user_id = ?
              AND restaurant_id = ?
              AND clock_out IS NULL
            ORDER BY clock_in DESC LIMIT 1
        ");
        $shift->execute([$user['id'], $restaurantId]);
        $shift = $shift->fetch();

        ok([
            'clocked_in'    => $shift ? true : false,
            'clock_in_time' => $shift ? $shift['clock_in'] : null,
            'hours_so_far'  => $shift ? $shift['hours_so_far'] : 0,
        ]);
        break;

    // ════════════════════════════════════════
    //  GET: Shift history for a staff member
    //  Used in manager payroll view
    // ════════════════════════════════════════
    case 'history':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $staffUserId = intval($_GET['id']   ?? 0);
        $from        = $_GET['from']        ?? date('Y-m-01'); // Start of month
        $to          = $_GET['to']          ?? date('Y-m-d');

        if (!$staffUserId) fail('Staff user ID required.');

        $stmt = $db->prepare("
            SELECT
                sl.id,
                sl.clock_in,
                sl.clock_out,
                CASE
                    WHEN sl.clock_out IS NOT NULL
                    THEN ROUND(TIMESTAMPDIFF(MINUTE, sl.clock_in, sl.clock_out) / 60, 2)
                    ELSE ROUND(TIMESTAMPDIFF(MINUTE, sl.clock_in, NOW()) / 60, 2)
                END AS hours_worked,
                CASE WHEN sl.clock_out IS NULL THEN 1 ELSE 0 END AS is_open
            FROM shift_log sl
            WHERE sl.user_id = ?
              AND sl.restaurant_id = ?
              AND DATE(sl.clock_in) BETWEEN ? AND ?
            ORDER BY sl.clock_in DESC
        ");
        $stmt->execute([$staffUserId, $restaurantId, $from, $to]);
        $shifts = $stmt->fetchAll();

        $totalHours = array_sum(array_column($shifts, 'hours_worked'));

        ok([
            'shifts'      => $shifts,
            'total_hours' => round($totalHours, 2),
            'from'        => $from,
            'to'          => $to,
        ]);
        break;

    // ════════════════════════════════════════
    //  GET: Who is currently on shift
    //  Used on manager overview
    // ════════════════════════════════════════
    case 'on_shift':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $stmt = $db->prepare("
            SELECT
                u.id, u.name, u.staff_id, u.role,
                sl.clock_in,
                ROUND(TIMESTAMPDIFF(MINUTE, sl.clock_in, NOW()) / 60, 2) AS hours_so_far
            FROM shift_log sl
            JOIN users u ON sl.user_id = u.id
            WHERE sl.restaurant_id = ?
              AND sl.clock_out IS NULL
              AND DATE(sl.clock_in) = CURDATE()
            ORDER BY sl.clock_in ASC
        ");
        $stmt->execute([$restaurantId]);
        ok($stmt->fetchAll());
        break;

    // ════════════════════════════════════════
    //  GET: Full payroll summary for a period
    //  Calculates hours x rate OR fixed salary
    //  for every staff member
    // ════════════════════════════════════════
    case 'payroll':
        requireMethod('GET');
        requireRole(['manager', 'admin']);

        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        // Get all staff with their pay settings
        $staffList = $db->prepare("
            SELECT
                u.id, u.name, u.staff_id, u.role,
                IFNULL(u.pay_type, 'hourly') AS pay_type,
                IFNULL(u.hourly_rate, 0)     AS hourly_rate,
                IFNULL(u.monthly_salary, 0)  AS monthly_salary
            FROM users u
            WHERE u.restaurant_id = ?
              AND u.is_active = 1
            ORDER BY u.role ASC, u.name ASC
        ");
        $staffList->execute([$restaurantId]);
        $staffMembers = $staffList->fetchAll();

        $payroll = [];
        foreach ($staffMembers as $s) {

            // Get total hours for this period
            $hoursStmt = $db->prepare("
                SELECT
                    ROUND(
                        SUM(
                            TIMESTAMPDIFF(MINUTE,
                                clock_in,
                                IFNULL(clock_out, NOW())
                            )
                        ) / 60
                    , 2) AS total_hours,
                    COUNT(*) AS shift_count
                FROM shift_log
                WHERE user_id = ?
                  AND restaurant_id = ?
                  AND DATE(clock_in) BETWEEN ? AND ?
            ");
            $hoursStmt->execute([$s['id'], $restaurantId, $from, $to]);
            $hrs = $hoursStmt->fetch();

            $totalHours  = floatval($hrs['total_hours']  ?? 0);
            $shiftCount  = intval($hrs['shift_count']    ?? 0);

            // Calculate gross pay
            if ($s['pay_type'] === 'salary') {
                // Pro-rate monthly salary for the period
                $daysInMonth  = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($from)), date('Y', strtotime($from)));
                $periodDays   = (strtotime($to) - strtotime($from)) / 86400 + 1;
                $grossPay     = ($s['monthly_salary'] / $daysInMonth) * $periodDays;
            } else {
                // Hourly: hours x rate
                $grossPay = $totalHours * $s['hourly_rate'];
            }

            $payroll[] = [
                'id'             => $s['id'],
                'name'           => $s['name'],
                'staff_id'       => $s['staff_id'],
                'role'           => $s['role'],
                'pay_type'       => $s['pay_type'],
                'hourly_rate'    => floatval($s['hourly_rate']),
                'monthly_salary' => floatval($s['monthly_salary']),
                'total_hours'    => $totalHours,
                'shift_count'    => $shiftCount,
                'gross_pay'      => round($grossPay, 2),
            ];
        }

        $totalPayroll = array_sum(array_column($payroll, 'gross_pay'));

        ok([
            'payroll'       => $payroll,
            'total_payroll' => round($totalPayroll, 2),
            'from'          => $from,
            'to'            => $to,
        ]);
        break;

    // ════════════════════════════════════════
    //  POST: Set pay type and rate for a staff member
    //  Manager only
    // ════════════════════════════════════════
    case 'set_pay':
        requireMethod('POST');
        requireRole(['manager', 'admin']);

        $body        = jsonBody();
        $id          = intval($body['id']             ?? 0);
        $payType     = trim($body['pay_type']         ?? 'hourly'); // 'hourly' or 'salary'
        $hourlyRate  = floatval($body['hourly_rate']  ?? 0);
        $monthlySal  = floatval($body['monthly_salary'] ?? 0);

        if (!$id) fail('Staff ID required.');
        if (!in_array($payType, ['hourly','salary'])) fail('Pay type must be hourly or salary.');
        if ($payType === 'hourly'  && $hourlyRate <= 0) fail('Please enter a valid hourly rate.');
        if ($payType === 'salary'  && $monthlySal <= 0) fail('Please enter a valid monthly salary.');

        $db->prepare("
            UPDATE users
            SET pay_type = ?, hourly_rate = ?, monthly_salary = ?
            WHERE id = ? AND restaurant_id = ?
        ")->execute([$payType, $hourlyRate, $monthlySal, $id, $restaurantId]);

        $s = $db->prepare("SELECT name, staff_id FROM users WHERE id = ?");
        $s->execute([$id]);
        $s = $s->fetch();

        $detail = $payType === 'hourly'
            ? "{$s['name']} ({$s['staff_id']}) pay set to hourly @ R$hourlyRate/hr"
            : "{$s['name']} ({$s['staff_id']}) pay set to salary @ R$monthlySal/month";

        auditLog($db, $restaurantId, 'change', $detail);
        ok(['id' => $id], $detail);
        break;

    default:
        fail('Unknown action: ' . htmlspecialchars($action));
}
?>
