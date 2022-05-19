<?php

namespace Tests\Search;

use MercuryHolidays\Search\Constants;
use MercuryHolidays\Search\Searcher;
use PHPUnit\Framework\TestCase;

class SearcherTest extends TestCase
{
    /**
     * $searcher
     *
     * @var Searcher
     */
    protected Searcher $searcher;

    public function setUp(): void
    {
        $this->searcher = new Searcher();
    }

    public function testRoomPropertiesValidation(): void
    {
        $this->expectExceptionMessage('Kindly check the Room Properties ');

        $this->searcher->add([
            Constants::AVAILABLE => true,
            Constants::GROUND_FLOOR     => 1,
            Constants::HOTEL     => 'Hotel 1',
            Constants::NUMBER    => 2,
        ]);
    }

    public function testOnlyAvailableRoomsAreAdded(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $this->assertCount(6, $searcher->availableRooms);
    }

    public function testAddedAvailableRoomsAreSortedByPrice(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $availableRooms = $searcher->availableRooms;

        $this->assertSame($availableRooms[ 0 ][ Constants::PRICE ], 25.80);

        $this->assertSame($availableRooms[ 2 ][ Constants::PRICE ], 35.00);

        $this->assertSame($availableRooms[ 3 ][ Constants::PRICE ], 45.80);

        $this->assertSame($availableRooms[ 5 ][ Constants::PRICE ], 45.80);
    }

    public function testIfSearchMethodReturnsEmptyArray(): void
    {
        $this->assertEmpty($this->searcher->search(2, 50, 100));
    }

    public function testGetRoomsMinAndMaxBudget(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $availableRoomsMinAndMaxBudget = $searcher->maxMinBudgetRoom($searcher->availableRooms, 20, 30);

        $this->assertCount(2, $availableRoomsMinAndMaxBudget);

        $this->assertSame($availableRoomsMinAndMaxBudget[ 0 ][ Constants::PRICE ], 25.80);

        $this->assertSame($availableRoomsMinAndMaxBudget[ 1 ][ Constants::PRICE ], 25.80);
    }

    public function testThatGetRoomWithMinimumBudgetIndexMethodReturnsRoomWithMinimumBudgetIndex(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $availableRoomWithMinimumBudgetIndex = $searcher->getRoomWithMinBudget($searcher->availableRooms, 20);

        $this->assertSame($availableRoomWithMinimumBudgetIndex, 0);
    }

    public function testThatGetRoomWithMaximumBudgetIndexMethodReturnsRoomWithMaximumBudgetIndex(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $availableRoomWithMaxBudget = $searcher->getRoomWithMaxBudget($searcher->availableRooms, 0, 30);

        $this->assertSame($availableRoomWithMaxBudget, 1);
    }

    public function testAvailableRoomsWithinMinMaxBudget(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $availableRoomsMinAndMaxBudget = $searcher->maxMinBudgetRoom($searcher->availableRooms, 30, 50);

        $roomsReturnedBySearchMethod = $searcher->search(1, 30, 50);

        $this->assertSame($roomsReturnedBySearchMethod, $availableRoomsMinAndMaxBudget);
    }

    public function testSearchMethodDoesNotReturnsMinAndMaxBudget(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $availableRoomsMinAndMaxBudget = $searcher->maxMinBudgetRoom($searcher->availableRooms, 30, 50);

        $roomsReturnedBySearchMethod = $searcher->search(2, 30, 50);

        $this->assertNotSame($roomsReturnedBySearchMethod, $availableRoomsMinAndMaxBudget);
    }

    public function testSearchMethodReturnsRoomsAdjAndOnTheSameFloor(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $roomsReturnedBySearchMethod = $searcher->search(2, 30, 50);

        $this->assertCount(2, $roomsReturnedBySearchMethod);

        $this->assertSame($roomsReturnedBySearchMethod[ 0 ][ Constants::GROUND_FLOOR ], 1);

        $this->assertSame($roomsReturnedBySearchMethod[ 1 ][ Constants::GROUND_FLOOR ], 1);

        $this->assertSame($roomsReturnedBySearchMethod[ 0 ][ Constants::NUMBER ], 3);

        $this->assertSame($roomsReturnedBySearchMethod[ 1 ][ Constants::NUMBER ], 4);
    }

    public function testIfSearchMethodPassesExample1(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $roomsReturnedBySearchMethod = $searcher->search(2, 20, 30);

        $this->assertSame($roomsReturnedBySearchMethod, [
            [
                Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 3, Constants::PRICE => 25.80
            ],
            [
                Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 4, Constants::PRICE => 25.80
            ]
        ]);
    }

    public function testIfSearchMethodPassesExample2(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $roomsReturnedBySearchMethod = $searcher->search(2, 30, 50);

        $this->assertSame($roomsReturnedBySearchMethod, [
            [
                Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 3, Constants::PRICE => 45.80
            ],
            [
                Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 4, Constants::PRICE => 45.80
            ]
        ]);
    }

    public function testIfSearchMethodPassesExample3(): void
    {
        $searcher = $this->addRoomsToSearcher($this->searcher);

        $roomsReturnedBySearchMethod = $searcher->search(1, 25, 40);

        $this->assertSame($roomsReturnedBySearchMethod, [
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 3, Constants::PRICE => 25.80 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 4, Constants::PRICE => 25.80 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 2, Constants::NUMBER => 7, Constants::PRICE => 35.00 ]
        ]);
    }

    public function testIfGetIndexOfRoomWithMinRoomNo(): void
    {
        $this->assertSame(0, $this->searcher->getRoomWithMinRoomNo($this->rooms(), 0));

        $this->assertSame(7, $this->searcher->getRoomWithMinRoomNo($this->rooms(), 1));
    }

    public function textIfgetHotelNameMethodRemovesSpecialCharactersFromString(): void
    {
        $this->assertSame('hotel-a', $this->searcher->getHotelName('@hotel /a*'));
    }

    public function textIfgetHotelNameMethodChangesSpacesToHyphen(): void
    {
        $this->assertSame('hotel-a-b-also', $this->searcher->getHotelName('@hotel /a* and b also'));
    }

    public function textIfgetHotelNameMethodChangesAllTextToLowercase(): void
    {
        $this->assertSame('hotel-a-b-also', $this->searcher->getHotelName('@Hotel /A* And B Also'));
    }

    public function rooms():array
    {
        return [
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 1, Constants::PRICE => 25.80 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 2, Constants::PRICE => 25.80 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 3, Constants::PRICE => 25.80 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 4, Constants::PRICE => 25.80 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 5, Constants::PRICE => 25.80 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 2, Constants::NUMBER => 6, Constants::PRICE => 30.10 ],
            [ Constants::HOTEL => 'Hotel A' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 2, Constants::NUMBER => 7, Constants::PRICE => 35.00 ],
            [ Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 1, Constants::PRICE => 45.80 ],
            [ Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 2, Constants::PRICE => 45.80 ],
            [ Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 3, Constants::PRICE => 45.80 ],
            [ Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => true,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 4, Constants::PRICE => 45.80 ],
            [ Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 1, Constants::NUMBER => 5, Constants::PRICE => 45.80 ],
            [ Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 2, Constants::NUMBER => 6, Constants::PRICE => 49.00 ],
            [ Constants::HOTEL => 'Hotel B' , Constants::AVAILABLE => false,
                Constants::GROUND_FLOOR => 2, Constants::NUMBER => 7, Constants::PRICE => 49.00 ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function addRoomsToSearcher(Searcher $searcher): Searcher
    {
        foreach ($this->rooms() as $key => $room) {
            $searcher->add($room);
        }
        return $searcher;
    }
}
