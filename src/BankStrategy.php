<?php

interface BankStrategy {
    public function setFile(string $file);
    public function isValid():bool;
    public function process():array;
}