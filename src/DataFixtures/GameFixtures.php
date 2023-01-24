<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\Level;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $game = new Game();
        $game->setTitle('Find The Flag');
        $game->setDescription(
            'The objective of this game is to find the name of the country that matches the flag that is displayed.
             This game has several levels of difficulty. 
             The flags are chosen randomly among all the flags of the world. 
             You can play alone or with others.'
        );
        $game->setTags('quizz, find-the-flag');
        $game->setImg('https://wallpapercave.com/wp/wp2972402.jpg');
        $game->setServer('http://localhost:8000/api/game-page/find-the-flag');

        $levelEasy = new Level();
        $levelEasy->setLabel('EASY');
        $levelEasy->setDifficulty(1);
        $levelEasy->setDescription('This level offers 3 flags to find');

        $game->addLevel($levelEasy);

        $levelNormal = new Level();
        $levelNormal->setLabel('NORMAL');
        $levelNormal->setDifficulty(5);
        $levelNormal->setDescription('This level offers 15 flags to find');

        $game->addLevel($levelNormal);

        $levelHard = new Level();
        $levelHard->setLabel('HARD');
        $levelHard->setDifficulty(10);
        $levelHard->setDescription('This level offers 30 flags to find');

        $game->addLevel($levelHard);

        $manager->persist($game);
        $manager->flush();
    }
}
