<?php

namespace App\Enums;

final class ApiStatusCode
{
    const OK = 1000;
    const NOT_EXISTED = 9992;
    const NO_DATA = 9994;
    const UNKNOW_ERROR = 1005;
    const MAXIMUM_SIZE_OF_FILE = 1008;
    const REQUIRE_PERMISSION_ACCESS = 1009;
    const PARAMETER_NOT_ENOUGH = 1002;
    const PARAMETER_TYPE_INVALID = 1003;
    const LOST_CONNECTED = 1001;
    const NOT_VALIDATE = 9995;
}
