<?php declare(strict_types=1);
include "SingaporeBank.php";

class BankService{

    const ACTION_SINGAPORE = "singapore";

    public BankStrategy $strategy;

    public function setStrategy(BankStrategy $strategy){
        $this->strategy = $strategy;
    }
    
    public function getResult(string $file, string $action):?array
    {
        try{
            if(self::ACTION_SINGAPORE == $action){
                $bank = new SingaporeBank();
                $bank->setFile($file);
                return $bank->process();
            }
        }catch(Exception $e){
            throw new ErrorException($e->getMessage());
        }


        return null;
    }
}