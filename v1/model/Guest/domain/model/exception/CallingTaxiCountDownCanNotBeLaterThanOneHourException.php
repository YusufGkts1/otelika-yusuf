<?php 

namespace model\Sdm\domain\model\exception;

class CallingTaxiCountDownCanNotBeLaterThanOneHourException extends \Exception {
    protected $message = 'Calling Taxi countdown can not be later than one hour.';
    protected $code = 2001;
}

?>