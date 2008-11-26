<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision: 589 $
 * @license GPLv3
 */

/**
 * Tests for the SQLite cache meta data handler
 */
class vcsGitCliCheckoutTests extends vcsTestCase
{
    /**
     * Return test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
	public static function suite()
	{
		return new PHPUnit_Framework_TestSuite( __CLASS__ );
	}

    public function setUp()
    {
        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        vcsCache::initialize( $this->createTempDir() );
    }

    public function testInitializeInvalidCheckout()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );

        try
        {
            $repository->initialize( 'file:///hopefully/not/existing/git/repo' );
            $this->fail( 'Expected pbsSystemProcessNonZeroExitCodeException.' );
        } catch ( pbsSystemProcessNonZeroExitCodeException $e )
        { /* Expected */ }

    }

    public function testInitializeCheckout()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

        $this->assertFalse( $repository->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );

        // Copy the repository to not chnage the test reference repository
        $repDir = $this->createTempDir() . '/git';
        self::copyRecursive( realpath( dirname( __FILE__ ) . '/../data/git' ), $repDir );
        $repository->initialize( 'file://' . $repDir );

        // Manually execute update in repository
        file_put_contents( $this->tempDir . '/' . ( $file = 'another' ), 'Some test contents' );
        $git = new vcsGitCliProcess();
        $git->workingDirectory( $this->tempDir );
        $git->argument( 'add' )->argument( $file )->execute();
        $git = new vcsGitCliProcess();
        $git->workingDirectory( $this->tempDir );
        $git->argument( 'commit' )->argument( $file )->argument( '-m' )->argument( '- Test commit.' )->execute();

        $this->assertTrue( $repository->update(), "Repository should have had an update available." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testGetVersionString()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

        $this->assertSame(
            "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            $repository->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

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

        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );
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
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

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
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

        $this->assertEquals(
            'kore',
            $repository->getAuthor()
        );
    }

    public function testGetLog()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

        $this->assertEquals(
            array(
                "43fb423f4ee079af2f3cba4e07eb8b10f4476815" => new vcsLogEntry(
                    "43fb423f4ee079af2f3cba4e07eb8b10f4476815", "kore", "- Added a first test file\n", 1226920616
                ),
                "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf" => new vcsLogEntry(
                    "16d59ca5905f40aba24d0efb6fc5f0d82ab65fbf", "kore", "- Added some test directories\n", 1226921143
                ),
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85" => new vcsLogEntry(
                    "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "kore", "- Renamed directory\n", 1226921195
                ),
                "2037a8d0efd4e51a4dd84161837f8865cf7d34b1" => new vcsLogEntry(
                    "2037a8d0efd4e51a4dd84161837f8865cf7d34b1", "kore", "- Modified file\n", 1226921232
                ),
            ),
            $repository->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

        $this->assertEquals(
            new vcsLogEntry(
                "8faf65e1c48d4908d48a647c1d23df54e1e15e85", "kore", "- Renamed directory\n", 1226921195
            ),
            $repository->getLogEntry( "8faf65e1c48d4908d48a647c1d23df54e1e15e85" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

        try {
            $repository->getLogEntry( "no_such_version" );
            $this->fail( 'Expected vcsNoSuchVersionException.' );
        } catch ( vcsNoSuchVersionException $e )
        { /* Expected */ }
    }

    public function testIterateCheckoutContents()
    {
        $repository = new vcsGitCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . realpath( dirname( __FILE__ ) . '/../data/git' ) );

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
}
