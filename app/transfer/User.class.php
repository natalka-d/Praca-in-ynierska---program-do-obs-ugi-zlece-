<?php
namespace app\transfer;

class User {
    public $login;
    public $role;
    public $id; // To pole musi tu być!

    public function __construct($login, $role, $id = null) {
        $this->login = $login;
        $this->role = $role;
        $this->id = $id;
    }
}