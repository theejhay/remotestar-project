<?php

namespace MercuryHolidays\Search;

use Exception;

class Searcher
{
    /**
     *
     * @var array
     */
    public array $availableRooms = [];

    /**
     *
     * @param array $roomsProperty
     * @return void
     * @throws Exception
     */
    public function add(array $roomsProperty): void
    {
        $this->validateRoomProperties($roomsProperty);

        if (!$roomsProperty[Constants::AVAILABLE]) {
            return;
        }

        if (!count($this->availableRooms)) {
            $this->availableRooms[] = $roomsProperty;
            return;
        }

        $this->addRoom($this->availableRooms, $roomsProperty);
    }

    /**
     *
     * @param integer $roomsRequired
     * @param integer $minimum
     * @param integer $maximum
     * @return array
     */
    public function search(int $roomsRequired, int $minimum, int $maximum): array
    {
        if (!count($this->availableRooms)) {
            return [];
        }

        $maxMinBudgetRoom = $this->maxMinBudgetRoom($this->availableRooms, $minimum, $maximum);

        if ($roomsRequired <= 1) {
            return $maxMinBudgetRoom;
        }

        return $this->getAdjRoomsSameFloor($maxMinBudgetRoom, $roomsRequired);
    }

    /**
     *
     * @param array $rooms
     * @param integer $minimumPrice
     * @param integer $maximumPrice
     * @return array
     */
    public function maxMinBudgetRoom(array $rooms, int $minimumPrice, int $maximumPrice): array
    {
        $roomWithMinBudget = $this->getRoomWithMinBudget($rooms, $minimumPrice);

        $roomWithMaxBudget = $this->getRoomWithMaxBudget($rooms, $roomWithMinBudget, $maximumPrice);

        $length = ($roomWithMaxBudget - $roomWithMinBudget) + 1;

        return array_slice($rooms, $roomWithMinBudget, $length);
    }

    /**
     *
     * @param array $rooms
     * @param integer $minimumPrice
     * @return integer
     */
    public function getRoomWithMinBudget(array $rooms, int $minimumPrice): int
    {
        $roomWithMinBudget = 0;

        $roomWithMaxBudget = count($rooms) - 1;

        while ($roomWithMinBudget < $roomWithMaxBudget) {
            $midIndex = floor(($roomWithMinBudget + $roomWithMaxBudget) / 2);

            if ($minimumPrice <= $rooms[$midIndex][Constants::PRICE]) {
                $roomWithMaxBudget = $midIndex;
            } else {
                $roomWithMinBudget = $midIndex + 1;
            }
        }

        return $roomWithMinBudget;
    }

    /**
     *
     * @param array $rooms
     * @return void
     */
    private function validateRoomProperties(array $rooms): void
    {
        $roomsProperty = array_keys($rooms);

        sort($roomsProperty);

        if (Constants::constants() !== $roomsProperty) {
            throw new \RuntimeException('Kindly check the Room Properties ');
        }
    }

    /**
     *
     * @param array $rooms
     * @param array $room
     * @return void
     */
    public function addRoom(array $rooms, array $room): void
    {
        for ($i = count($rooms) - 1; ($i >= 0 && $rooms[$i][Constants::PRICE] > $room[Constants::PRICE]); $i--
        ) {
            $rooms[$i + 1] = $rooms[$i];
        }

        $rooms[$i + 1] = $room;

        $this->availableRooms = $rooms;
    }

    /**
     *
     * @param string $hotelName
     * @return string
     */
    public function getHotelName(string $hotelName): string
    {
        $hotelName = strtolower(str_replace(' ', '-', $hotelName));

        return preg_replace('/[^A-Za-z0-9\-]/', '', $hotelName);
    }


    /**
     *
     * @param array $rooms
     * @param integer $roomWithMinBudget
     * @param integer $maximumPrice
     * @return integer
     */
    public function getRoomWithMaxBudget(array $rooms, int $roomWithMinBudget, int $maximumPrice): int
    {
        $roomWithMaxBudget = count($rooms) - 1;

        while ($roomWithMinBudget < $roomWithMaxBudget) {
            $mid = floor((($roomWithMinBudget + $roomWithMaxBudget) / 2) + 1);

            $maximumPrice < $rooms[$mid][Constants::PRICE] ?
                $roomWithMaxBudget = $mid  - 1 : $roomWithMinBudget = (int)$mid;
        }

        return $roomWithMaxBudget;
    }

    /**
     *
     * @param array $rooms
     * @param integer $roomsRequired
     * @return array
     */
    public function getAdjRoomsSameFloor(array $rooms, int $roomsRequired): array
    {
        $result = [];

        $adjacentRooms = [];

        foreach ($rooms as $roomIndex => $roomIndexValue) {
            $roomWithMinRoomNo = $this->getRoomWithMinRoomNo($rooms, $roomIndex);

            if ($roomIndex !== $roomWithMinRoomNo) {
                $lesser = $rooms[$roomWithMinRoomNo];

                $rooms[$roomWithMinRoomNo] = $roomIndexValue;

                $rooms[$roomIndex] = $lesser;
            }

            $room = $roomIndexValue;

            $hotelIndex = $this->getHotelName($room[Constants::HOTEL]);

            $floorIndex = $room[Constants::GROUND_FLOOR];

            if (!isset($adjacentRooms[$hotelIndex])) {
                $adjacentRooms[$hotelIndex] = [];
            }

            if (!isset($adjacentRooms[$hotelIndex][$floorIndex])) {
                $adjacentRooms[$hotelIndex][$floorIndex] = [];
            }

            if (!count($adjacentRooms[$hotelIndex][$floorIndex])) {
                $adjacentRooms[$hotelIndex][$floorIndex] = [$room];
                continue;
            }

            $floorRooms = $adjacentRooms[$hotelIndex][$floorIndex];

            $lastAdjRoomNo = $floorRooms[count($floorRooms) - 1][Constants::NUMBER] + 1;

            if ($lastAdjRoomNo === $room[Constants::NUMBER]) {
                $adjacentRooms[$hotelIndex][$floorIndex][] = $room;
            } else {
                $adjacentRooms[$hotelIndex][$floorIndex] = [$room];
            }

            if (count($adjacentRooms[$hotelIndex][$floorIndex]) === $roomsRequired) {
                $floorRooms = $adjacentRooms[$hotelIndex][$floorIndex];

                $result = [...$result, ...$floorRooms];

                $adjacentRooms[$hotelIndex][$floorIndex] = [];
            }
        }

        return $result;
    }

    /**
     *
     * @param array $rooms
     * @param integer $roomIndex
     * @return integer
     */
    public function getRoomWithMinRoomNo(array $rooms, int $roomIndex): int
    {
        $roomWithMinRoomNo = $roomIndex;

        for ($i = $roomIndex + 1, $iMax = count($rooms); $i < $iMax; $i++) {
            if ($rooms[$i][Constants::NUMBER] < $rooms[$roomWithMinRoomNo][Constants::NUMBER]) {
                $roomWithMinRoomNo = $i;
            }
        }

        return $roomWithMinRoomNo;
    }
}
