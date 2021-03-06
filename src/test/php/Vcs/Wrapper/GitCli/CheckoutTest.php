<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\GitCli;

use \Vcs\TestCase;

use \Vcs\Cache;
use \Vcs\LogEntry;

/**
 * Tests for the SQLite cache meta data handler
 */
class CheckoutTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'git' ) )
        {
            $this->markTestSkipped( 'Git binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        Cache::initialize( $this->createTempDir() );
    }

    /**
     * @return void
     * @expectedException \SystemProcess\NonZeroExitCodeException
     */
    public function testInitializeInvalidCheckout()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file:///hopefully/not/existing/git/repo' );
    }

    public function testInitializeCheckout()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        $this->markTestSkipped( 'Git does not allow the necessary commit anymore by default - thus we can\'t test this properly.' );

        $repDir = $this->createTempDir() . '/git';
        self::copyRecursive( $this->extractRepository( 'git' ), $repDir );

        // Copy the repository to not chnage the test reference repository
        $checkin = new Checkout( $this->tempDir . '/ci' );
        $checkin->initialize( 'file://' . $repDir );

        $checkout = new Checkout( $this->tempDir . '/co' );
        $checkout->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $this->tempDir . '/ci/another', 'Some test contents' );
        $git = new Process();
        $git->workingDirectory( $this->tempDir . '/ci' );
        $git->argument( 'add' )->argument( 'another' )->execute();
        
        $git = new Process();
        $git->workingDirectory( $this->tempDir . '/ci' );
        $git->argument( 'commit' )->argument( 'another' )->argument( '-m' )->argument( '- Test commit.' )->execute();

        $git = new Process();
        $git->workingDirectory( $this->tempDir . '/ci' );
        $git->argument( 'push' )->argument( 'origin' )->execute();

        $this->assertTrue( $checkin->update(), "Checkin repository should have had an update available." );

        $this->assertFileNotExists( $this->tempDir . '/co/another' );
        $this->assertTrue( $checkout->update(), "Checkout repository should have had an update available." );
        $this->assertFileExists( $this->tempDir . '/co/another' );
    }

    public function testGetVersionString()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertSame(
            "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertSame(
            array(
                "43fb423f4ee079af2f3cba4e07eb8b10f4476815",
                "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf",
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85",
                "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            ),
            $repository->getVersions()
        );
    }

    public function testUpdateCheckoutToOldVersion()
    {
        $this->markTestSkipped( 'Downgrade seems not to remove files from checkout.' );

        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $this->assertTrue(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" in checkout.'
        );

        $repository->update( "43fb423f4ee079af2f3cba4e07eb8b10f4476815" );

        $this->assertFalse(
            file_exists( $this->tempDir . '/dir1/file' ),
            'Expected file "/dir1/file" not in checkout.'
        );
    }

    public function testCompareVersions()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertTrue(
            $repository->compareVersions( "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf", "2037a8d0efd4e51a4dd84161837f8865cf7d34b1" ) < 0
        );

        $this->assertTrue(
            $repository->compareVersions( "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf", "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf" ) == 0
        );

        $this->assertTrue(
            $repository->compareVersions( "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "43fb423f4ee079af2f3cba4e07eb8b10f4476815" ) > 0
        );
    }

    public function testGetAuthor()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertEquals(
            'kore',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertEquals(
            array(
                "43fb423f4ee079af2f3cba4e07eb8b10f4476815" => new LogEntry(
                    "43fb423f4ee079af2f3cba4e07eb8b10f4476815", "kore", "- Added a first test file\n", 1226920616
                ),
                "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf" => new LogEntry(
                    "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf", "kore", "- Added some test directories\n", 1226921143
                ),
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85" => new LogEntry(
                    "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "kore", "- Renamed directory\n", 1226921195
                ),
                "2037a8d0efd4e51a4dd84161837f8865cf7d34b1" => new LogEntry(
                    "2037a8d0efd4e51a4dd84161837f8865cf7d34b1", "kore", "- Modified file\n", 1226921232
                ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertEquals(
            new LogEntry(
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "kore", "- Renamed directory\n", 1226921195
            ),
            $repository->getLogEntry( "8faf65e1c48d4908d48a647c1d23df54e1e15e85" )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetUnknownLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $repository->getLogEntry( "no_such_version" );
    }

    public function testIterateCheckoutContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $files = array();
        foreach ( $repository as $file )
        {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testGetCheckout()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertSame(
            $repository->get(),
            $repository
        );

        $this->assertSame(
            $repository->get( '/' ),
            $repository
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\FileNotFoundException
     */
    public function testGetInvalid()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $repository->get( '/../' );
    }

    public function testGetDirectory()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $this->assertEquals(
            $repository->get( '/file' ),
            new File( $this->tempDir, '/file' )
        );
    }
}

