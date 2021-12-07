<?php 

namespace model\Sdm\domain\model\exception;

class TaxiCountDownCanNotBeLaterThanOneHourException extends \Exception {
    protected $message = 'Taxi countdown can not be later than one hour.';
    protected $code = 2001;
}

?>