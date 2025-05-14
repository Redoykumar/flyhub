<?php
namespace Redoy\FlyHub\DTOs\Shared;

class PassengerDTO
{
    public $firstName;
    public $lastName;
    public $gender;
    public $dob;
    public $passportNumber;
    public $passportExpiry;
    public $nationality;
    public $passportIssuedCountry;
    public $type;

    public function __construct(array $data)
    {
        $this->firstName = $data['first_name'] ?? null;
        $this->lastName = $data['last_name'] ?? null;
        $this->gender = $data['gender'] ?? null;
        $this->dob = $data['dob'] ?? null;
        $this->passportNumber = $data['passport_number'] ?? null;
        $this->passportExpiry = $data['passport_expiry'] ?? null;
        $this->nationality = $data['nationality'] ?? null;
        $this->passportIssuedCountry = $data['passport_issued_country'] ?? null;
        $this->type = $data['type'] ?? 'ADT';
    }
}
