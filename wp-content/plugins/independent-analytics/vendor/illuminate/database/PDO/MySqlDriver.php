<?php

namespace IAWP_SCOPED\Illuminate\Database\PDO;

use IAWP_SCOPED\Doctrine\DBAL\Driver\AbstractMySQLDriver;
use IAWP_SCOPED\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;
class MySqlDriver extends AbstractMySQLDriver
{
    use ConnectsToDatabase;
}
