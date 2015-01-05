<?php

namespace BaseKit\Builder;

class AccountHolderBuilder
{
    protected $accountHolderRef = 0;
    protected $brandRef = 0;
    protected $username = null;
    protected $password = null;
    protected $firstName = null;
    protected $lastName = null;
    protected $email = null;
    protected $languageCode = 'en';

    public function __construct()
    {
    }

    public function getRef()
    {
        return $this->accountHolderRef;
    }

    public function setRef($accountHolderRef)
    {
        $this->accountHolderRef = $accountHolderRef;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getBrandRef()
    {
        return $this->brandRef;
    }

    public function setBrandRef($brandRef)
    {
        $this->brandRef = $brandRef;
    }

    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    }
}
