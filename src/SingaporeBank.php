<?php
require "BankStrategy.php";

class SingaporeBank implements BankStrategy
{
    public string $file = "";
    public array $header;

    const HEADER_CURRENCY = 0;
    const HEADER_FAILURE_CODE = 1;
    const HEADER_FAILURE_MESSAGE = 2;

    const COLUMN_BANK_CODE = 0;
    const COLUMN_BRANCH_CODE = 2;
    const COLUMN_BAN = 6;
    const COLUMN_BANK_ACCOUNT_NAME = 7;
    const COLUMN_AMOUNT = 8;
    const COLUMN_E2E = 10;
    const COLUMN_NUMBER_11 = 11;

    public function setFile(string $file)
    {
        if (!file_exists($file)) { 
            throw new ErrorException('File does not exist');
        }
        $this->file = $file;
    }

    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    public function process(): array
    {
        $result = [];
        if (!$this->file) { 
            throw new Exception("No file set");
        }
        $document = fopen($this->file, "r");
        $this->setHeader(fgetcsv($document));

        $rcs = $this->processLines($document);

        $result = [
            "filename" => basename($this->file),
            "document" => $document,
            "failure_code" => $this->header[self::HEADER_FAILURE_CODE],
            "failure_message" => $this->header[self::HEADER_FAILURE_MESSAGE],
            "records" => $rcs
        ];

        return $result;
    }

    private function processLines($document)
    {
        $rcs = [];
        while (!feof($document)) {
            $row = fgetcsv($document);
            if (!$this->isValidRow($row)) { 
                continue;
            }

            $rcs[] = [
                "amount" => [
                    "currency" => $this->header[self::HEADER_CURRENCY],
                    "subunits" => (int) ($this->getAmount($row) * 100)
                ],
                "bank_account_name" => $this->getBankAccountName($row),
                "bank_account_number" => $this->getBan($row),
                "bank_branch_code" => $this->getBranchCode($row),
                "bank_code" => $this->getBankCode($row),
                "end_to_end_id" => $this->getEndToEnd($row),
            ];;
        }

        return array_filter($rcs);
    }

    // Validating

    public function isValid(): bool
    {
        return true;
    }

    private function isValidRow(array $row)
    {
        return count($row) == 16;
    }

    // PROCESSING ROW

    private function getAmount(array $row)
    {
        return (float) $row[self::COLUMN_AMOUNT];
    }

    private function getBankCode(array $row)
    {
        return (int) $row[self::COLUMN_BANK_CODE];
    }

    private function getBan(array $row)
    {
        return (int) $row[self::COLUMN_BAN] ? (int) $row[self::COLUMN_BAN] : "Bank account number missing";
    }

    private function getBranchCode(array $row)
    {
        return !$row[self::COLUMN_BRANCH_CODE] ? "Bank branch code missing" : $row[self::COLUMN_BRANCH_CODE];
    }

    private function getEndToEnd(array $row)
    {
        return !$row[self::COLUMN_E2E] && !$row[self::COLUMN_NUMBER_11] ? "End to end id missing" : $row[self::COLUMN_E2E] . $row[self::COLUMN_NUMBER_11];
    }

    private function getBankAccountName(array $row)
    {
        return str_replace(" ", "_", strtolower($row[self::COLUMN_BANK_ACCOUNT_NAME]));
    }


    // END OF ROW PROCESSING

}
