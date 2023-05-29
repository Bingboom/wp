<?php

namespace IAWP_SCOPED\IAWP;

class Email_Reports
{
    public function __construct()
    {
        \add_filter('cron_schedules', [$this, 'add_monthly_schedule_cron']);
        $monitored_options = ['iawp_email_report_time', 'iawp_email_report_message', 'iawp_email_report_email_addresses'];
        foreach ($monitored_options as $option) {
            \add_action('update_option_' . $option, [$this, 'schedule_email_report'], 10, 0);
            \add_action('add_option_' . $option, [$this, 'schedule_email_report'], 10, 0);
        }
        \add_action('iawp_send_email_report', [$this, 'send_email_report']);
    }
    public function schedule_email_report()
    {
        $this->unschedule_email_report();
        if (empty(\IAWP_SCOPED\iawp()->get_option('iawp_email_report_email_addresses', []))) {
            return;
        }
        $delivery_time = new \DateTime('first day of +1 month', new \DateTimeZone(\wp_timezone_string()));
        $delivery_time->setTime(\IAWP_SCOPED\iawp()->get_option('iawp_email_report_time', 9), 0);
        \wp_schedule_event($delivery_time->getTimestamp(), 'monthly', 'iawp_send_email_report');
    }
    public function unschedule_email_report()
    {
        $timestamp = \wp_next_scheduled('iawp_send_email_report');
        \wp_unschedule_event($timestamp, 'iawp_send_email_report');
    }
    public function add_monthly_schedule_cron($schedules)
    {
        $schedules['monthly'] = ['interval' => \MONTH_IN_SECONDS, 'display' => \esc_html__('Once a Month', 'iawp')];
        return $schedules;
    }
    public function send_email_report(bool $test = \false)
    {
        $to = \IAWP_SCOPED\iawp()->get_option('iawp_email_report_email_addresses', []);
        if (empty($to)) {
            return;
        }
        $pdf = (new PDF())->create_and_save_pdf();
        if (\is_null($pdf)) {
            return;
        }
        $date = (new \DateTime('-1 month'))->format('F Y');
        $subject = \sprintf(\esc_html__('Analytics Report for %1$s [%2$s]', 'iawp'), \get_bloginfo('name'), $date);
        if ($test) {
            $subject = \esc_html__('[Test]', 'iawp') . ' ' . $subject;
        }
        $message = \IAWP_SCOPED\iawp()->get_option('iawp_email_report_message', \esc_html__("Please find the performance report attached to this email. It includes last month's views & visitors, as well as the top pages, referrers, and countries.", 'iawp'));
        $headers[] = 'From: ' . \get_bloginfo('name') . ' <' . \get_bloginfo('admin_email') . '>';
        return \wp_mail($to, $subject, $message, $headers, $pdf);
    }
}
