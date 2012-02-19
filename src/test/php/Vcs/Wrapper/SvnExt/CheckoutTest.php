<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\SvnExt;

use Vcs\TestCase;

/**
 * Tests for the SQLite cache meta data handler
 */
class CheckoutTest extends TestCase
{
    protected function setUp()
    {
        if ( !extension_loaded( 'svn' ) )
        {
            $this->markTestSkipped( 'Svn extension required to run this test.' );
        }

        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );
    }

    /**
     * @return void
     * @expectedException \vcsCheckoutFailedException
     */
    public function testInitializeInvalidCheckout()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        @$repository->initialize( 'file:///hopefully/not/existing/svn/repo' );
    }

    public function testInitializeCheckout()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        // Copy the repository to not chnage the test reference repository
        $repDir = $this->extractRepository( 'svn' );

        // Create two repositories one for the checkin one for the test checkout
        $checkin = new \vcsSvnExtCheckout( $this->tempDir . '/ci' );
        $checkin->initialize( 'file://' . $repDir );

        $checkout = new \vcsSvnExtCheckout( $this->tempDir . '/co' );
        $checkout->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $file = $this->tempDir . '/ci/another', 'Some test contents' );
        $svn = new \vcsSvnCliProcess();
        $svn->argument( 'add' )->argument( $file )->execute();
        $svn = new \vcsSvnCliProcess();
        $svn->argument( 'commit' )->argument( $file )->argument( '-m' )->argument( '- Test commit.' )->execute();

        $this->assertTrue( $checkin->update(), "Checkin repository should have had an update available." );

        $this->assertFileNotExists( $this->tempDir . '/co/another' );
        $this->assertTrue( $checkout->update(), "Checkout repository should have had an update available." );
        $this->assertFileExists( $this->tempDir . '/co/another' );
    }

    public function testGetVersionString()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertSame(
            "6",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertSame(
            array( "1", "2", "3", "4", "5", "6" ),
            $repository->getVersions()
        );
    }

    public function testUpdateCheckoutToOldVersion()
    {
        return $this->markTestSkipped( 'Update to earlier versions seems not to be supported by pecl/svn.' );

        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );

        $repository->update( "0" );

        $this->assertFalse(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" not in checkout.'
        );
    }

    public function testCompareVersions()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertTrue(
            $repository->compareVersions( "1", "2" ) < 0
        );

        $this->assertTrue(
            $repository->compareVersions( "2", "2" ) == 0
        );

        $this->assertTrue(
            $repository->compareVersions( "3", "2" ) > 0
        );
    }

    public function testGetAuthor()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertEquals(
            'kore',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertEquals(
            array(
                1 => new \vcsLogEntry(
                    '1',
                    'kore',
                    "- Added test file\n",
                    1226412609
                ),
                2 => new \vcsLogEntry(
                    '2',
                    'kore',
                    "- Added some test directories\n",
                    1226412647
                ),
                3 => new \vcsLogEntry(
                    '3',
                    'kore',
                    "- Renamed directory\n",
                    1226412664
                ),
                4 => new \vcsLogEntry(
                    '4',
                    'kore',
                    "- Added file in subdir\n",
                    1226592944
                ),
                5 => new \vcsLogEntry(
                    '5',
                    'kore',
                    "- Added another line to file\n",
                    1226595170
                ),
                6 => new \vcsLogEntry(
                    '6',
                    'kore',
                    "# Added binary to repository\n",
                    1228676322
                ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertEquals(
            new \vcsLogEntry(
                '2',
                'kore',
                "- Added some test directories\n",
                1226412647
            ),
            $repository->getLogEntry( "2" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \vcsNoSuchVersionException.' );
        } catch ( \vcsNoSuchVersionException $e )
        { /* Expected */ }
    }

    public function testIterateCheckoutContents()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $files = array();
        foreach ( $repository as $file )
        {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/binary',
                '/dir1/',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testGetCheckout()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertSame(
            $repository->get(),
            $repository
        );

        $this->assertSame(
            $repository->get( '/' ),
            $repository
        );
    }

    public function testGetInvalid()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        try
        {
            $repository->get( '/../' );
            $this->fail( 'Expected \vcsFileNotFoundException.' );
        }
        catch ( \vcsFileNotFoundException $e )
        { /* Expected */ }
    }

    public function testGetDirectory()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new \vcsSvnExtDirectory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new \vcsSvnExtCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $this->assertEquals(
            $repository->get( '/file' ),
            new \vcsSvnExtFile( $this->tempDir, '/file' )
        );
    }
}

