<?php

namespace SimpleAPI\Controllers;

use SimpleAPI\Controllers\ResponseController;
use SimpleAPI\Database\DBController;

class GETController
{
    public function __construct($request)
    {
        $this->request = $request;
        $this->table =
            explode("?", $request)[0] !== "relations"
                ? explode("?", $request)[0]
                : null;
        $this->select = $_GET["select"] ?? "*";
        $this->limit = $this->getLimit();
        $this->orderMode = $this->getOrder();
        $this->filters = $this->getFilters();
        $this->relations = $this->getRelations();
    }

    public function getOrder()
    {
        $by = $_GET["orderBy"] ?? null;
        $mode = $_GET["orderMode"] ?? null;
        return $by && $mode ? "ORDER BY $by $mode" : null;
    }

    public function getLimit()
    {
        $start = $_GET["startAt"] ?? null;
        $end = $_GET["endAt"] ?? null;

        return isset($_GET["startAt"]) ? "LIMIT $start, $end" : null;
    }

    public function getFilters()
    {
        $linkTo = $_GET["linkTo"] ?? null;
        $equalTo = $_GET["equalTo"] ?? null;

        $linkTo = explode(",", $linkTo);
        $equalTo = explode(",", $equalTo);

        $result = "";

        foreach ($linkTo as $key => $link) {
            if ($key === 0) {
                $result = "WHERE $link = '$equalTo[$key]'";
                continue;
            }

            $result .= "AND $link = '$equalTo[$key]'";
        }

        return isset($_GET["linkTo"]) ? $result : "";
    }

    public function getRelations()
    {
        if (!isset($_GET["rel"]) && !isset($_GET["type"])) {
            return null;
        }

        $relArray = $_GET["rel"];
        $relArray = explode(",", $relArray);

        $typeArray = $_GET["type"];
        $typeArray = explode(",", $typeArray);

        $result = "";

        foreach ($relArray as $key => $rel) {
            $key === 0 ? ($result .= $rel) : "";

            if ($key + 1 > count($relArray) - 1) {
                break;
            }

            $nextKey = $key + 1;

            $result .= " INNER JOIN $relArray[$nextKey] ON $relArray[0].id_$typeArray[$nextKey]_$typeArray[0] = $relArray[$nextKey].id_$typeArray[$nextKey]";
        }

        return isset($_GET["rel"]) ? $result : "";
    }

    public function response()
    {
        $query = "SELECT $this->select FROM $this->table $this->relations $this->filters $this->orderMode $this->limit";
        echo ResponseController::LogData(DBController::query($query));
    }
}
