<?php

namespace SimpleAPI\Controllers;

use SimpleAPI\Database\DBController;
use SimpleAPI\Controllers\JWTController;

class DELETEController
{
    public $response;

    public function __construct($request)
    {
        $this->table = explode("?", $request)[0];
        $this->id = $this->getId();
        $this->token = $this->getToken();
    }

    public function response()
    {
        if ($this->id && $this->token) {
            $id = $this->id[0];
            $nameId = $this->id[1];

            $query = "DELETE FROM $this->table WHERE $nameId = '$id' ";

            DBController::query($query);

            $this->response = ResponseController::LogData("The data was deleted successfully", "null");
        }

        echo $this->response;
    }

    public function getId()
    {
        if (isset($_GET["id"]) && isset($_GET["nameId"])) {
            $id = $_GET["id"];
            $nameId = $_GET["nameId"];

            $query = "SELECT * from $this->table WHERE $nameId = '$id'";
            $response = DBController::query($query);

            if ($response) {
                return [$id, $nameId];
            } else {
                $this->response = ResponseController::LogData(
                    "The id is not found in the database",
                    "null"
                );
                return false;
            }
        } else {
            return false;
        }
    }

    public function getToken()
    {
        if (
            isset(apache_request_headers()["Authorization"])
        ) {
            $token = apache_request_headers()["Authorization"];
            $token = JWTController::CheckToken($token);

            if ($token) {
                return true;
            } else {
                $this->response = ResponseController::LogError(
                    404,
                    "The user is not authorized"
                );
                return false;
            }
        } else {
            $this->response = ResponseController::LogError(
                404,
                "The user is not authorized"
            );
            return false;
        }
    }
}
