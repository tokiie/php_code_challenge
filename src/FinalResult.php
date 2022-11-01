<?php
require 'BankService.php';

class FinalResult
{
    public function results($file)
    {
        $bankService = new BankService();
        return $bankService->getResult($file, BankService::ACTION_SINGAPORE);
    }
}

?>