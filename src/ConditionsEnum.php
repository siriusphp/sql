<?php
declare(strict_types=1);

namespace Sirius\Sql;


class ConditionsEnum
{
    CONST EQUALS = '=';
    CONST NOT_EQUALS = '<>';
    CONST BETWEEN = 'between';
    CONST STARTS_WITH = 'starts_with';
    CONST ENDS_WITH = 'ends_with';
    CONST CONTAINS = 'contains';
    CONST DOES_NOT_START_WITH = 'does_not_start_with';
    CONST DOES_NOT_END_WITH = 'does_not_end_with';
    CONST DOES_NOT_CONTAIN = 'does_not_contain';
}