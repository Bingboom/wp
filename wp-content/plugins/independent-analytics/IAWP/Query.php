<?php

namespace IAWP_SCOPED\IAWP;

class Query
{
    const CAMPAIGN_URLS = 'campaign_urls';
    const CAMPAIGNS = 'campaigns';
    const REFERRER_GROUPS = 'referrer_groups';
    const REFERRERS = 'referrers';
    const RESOURCES = 'resources';
    const VIEWS = 'views';
    const VISITORS = 'visitors';
    const VISITORS_TMP = 'visitors_tmp';
    // Used in DB v7 migration
    const VISITORS_1_16_ARCHIVE = 'visitors_1_16_archive';
    // Used in DB v7 migration
    const SESSIONS = 'sessions';
    const WC_ORDERS = 'wc_orders';
    private $query;
    private $parameters;
    private $pdo;
    private $rows = [];
    private $last_inserted_id = 0;
    private $external_tables = ['wc_order_stats', 'comments'];
    public function __construct(string $query_name, ?array $parameters = [])
    {
        $this->query = $this->get_query($query_name);
        $this->parameters = $parameters;
    }
    public function last_inserted_id() : ?int
    {
        return $this->last_inserted_id;
    }
    /**
     * Responsible for fetching the SQL from the correct query file. Query files are stored as text
     * files with the txt extension to prevent being filtered out during backups and migrations by
     * various hosts.
     *
     * @param string $query_name
     *
     * @return string
     */
    private function get_query(string $query_name) : string
    {
        global $wpdb;
        $wordpress_table_prefix = $wpdb->prefix;
        $file_path = \IAWP_SCOPED\iawp_path_to("sql/{$query_name}.txt");
        $file_contents = \file_get_contents($file_path);
        $reflection = new \ReflectionClass(static::class);
        $constants = $reflection->getConstants();
        foreach ($constants as $key => $table_partial) {
            $hard_coded_table = 'wp_independent_analytics_' . $table_partial;
            $actual_table = $wordpress_table_prefix . 'independent_analytics_' . $table_partial;
            if ($actual_table !== $hard_coded_table) {
                $file_contents = \str_replace($hard_coded_table, $actual_table, $file_contents);
            }
        }
        foreach ($this->external_tables as $table_partial) {
            $hard_coded_table = 'wp_' . $table_partial;
            $actual_table = $wordpress_table_prefix . $table_partial;
            if ($actual_table !== $hard_coded_table) {
                $file_contents = \str_replace($hard_coded_table, $actual_table, $file_contents);
            }
        }
        return $file_contents;
    }
    public function rows() : array
    {
        return $this->rows;
    }
    public function row() : ?\stdClass
    {
        if (isset($this->rows[0])) {
            return $this->rows[0];
        } else {
            return null;
        }
    }
    public function inserted_id() : ?int
    {
        if ($this->last_inserted_id > 0) {
            return $this->last_inserted_id;
        } else {
            return null;
        }
    }
    public function run() : self
    {
        $this->open_connection();
        $statement = $this->pdo->prepare($this->query);
        foreach ($this->parameters as $key => $value) {
            $statement->bindValue($key, $value);
        }
        $statement->execute();
        $rows = $statement->fetchAll();
        $this->rows = \array_map(function ($row) {
            return (object) $row;
        }, $rows);
        $this->last_inserted_id = $this->pdo->lastInsertId();
        $this->close_connection();
        return $this;
    }
    public static function query(string $query_name, ?array $parameters = []) : self
    {
        $db = new self($query_name, $parameters);
        $db->run();
        return $db;
    }
    private function open_connection()
    {
        global $wpdb;
        $raw_host = $wpdb->dbhost;
        $name = $wpdb->dbname;
        $charset = $wpdb->dbcharset ?? 'utf8';
        $user = $wpdb->dbuser;
        $password = $wpdb->dbpassword;
        if ($host_data = $wpdb->parse_db_host($raw_host)) {
            list($host, $port, $socket, $is_ipv6) = $host_data;
        } else {
            \error_log('IAWP: Parsing DB host failed. Host = ' . $raw_host);
            return;
        }
        if ($is_ipv6 && \extension_loaded('mysqlnd')) {
            $host = "[{$host}]";
        }
        $charset_collate = $wpdb->determine_charset($charset, '');
        $charset = $charset_collate['charset'];
        if (isset($socket)) {
            $data_source_name = 'mysql:unix_socket=' . $socket . ';dbname=' . $name . ';charset=' . $charset;
        } else {
            $data_source_name = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=' . $charset;
            if (isset($port)) {
                $data_source_name .= ';port=' . $port;
            }
        }
        $this->pdo = new \PDO($data_source_name, $user, $password);
    }
    private function close_connection()
    {
        $this->pdo = null;
    }
    /**
     * Safe way to get the name of a table
     *
     * @param string $name
     *
     * @return string|null
     */
    public static function get_table_name(string $name) : ?string
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $reflection = new \ReflectionClass(static::class);
        $constants = $reflection->getConstants();
        if (\in_array($name, $constants)) {
            return $prefix . 'independent_analytics_' . $name;
        } else {
            return null;
        }
    }
}
