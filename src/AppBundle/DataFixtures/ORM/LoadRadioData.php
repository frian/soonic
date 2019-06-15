<?php

/**
 * This file is part of the Sooni package.
 *
 * (c) TimeTM <https://github.com/soonic>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Radio;

/**
 * User fixture
 *
 * @author André Friedli <a@frian.org>
 */
class LoadRadioData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {

        $radios = array('jazz', 'pop', 'rock', 'funk', 'blues', 'after', '60s', '70s', '80s', '90s',
            'techno', 'dance', 'dj', 'folk', 'punk', 'news', 'classics', 'worldmusic', 'smoothjazz', 'oldies');

    	/**
    	 * Add languages
    	 */
    	foreach ( $radios as $radioName ) {

	    	// create radio
	        $radio = new Radio();
	        $radio->setName("radio $radioName");
            $radio->setHomepageUrl("radio$radioName.com");
            $radio->setStreamUrl("radio$radioName.com");
	        // add reference for further fixtures
	        // $this->addReference('language'.$index, $radio);

	    	$manager->persist($radio);
	    	$manager->flush();
    	}
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
    	return 4; // the order in which fixtures will be loaded
    }
}
