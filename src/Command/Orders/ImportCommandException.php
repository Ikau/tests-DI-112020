<?php

class ImportCommandException extends Exception
{
    const ERROR_CODE_FILE_NOT_FOUND_CUSTOMER = 0;
    const ERROR_CODE_FILE_NOT_FOUND_ORDER = 1;
    const ERROR_CODE_CANNOT_OPEN_FILE_CUSTOMER = 2;
    const ERROR_CODE_CANNOT_OPEN_FILE_ORDER = 3;
}