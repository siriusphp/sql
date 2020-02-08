<?php
declare(strict_types=1);

namespace Sirius\Sql;

class ConditionsEnum
{
    const EQUALS = '=';
    const NOT_EQUALS = '<>';
    const BETWEEN = 'between';
    const STARTS_WITH = 'starts_with';
    const ENDS_WITH = 'ends_with';
    const CONTAINS = 'contains';
    const DOES_NOT_START_WITH = 'does_not_start_with';
    const DOES_NOT_END_WITH = 'does_not_end_with';
    const DOES_NOT_CONTAIN = 'does_not_contain';
}
