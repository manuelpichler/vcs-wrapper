<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\BzrCli;

use \Vcs\TestCase;

use \Vcs\Diff\Chunk;
use \Vcs\Diff\Line;

/**
 * @group bazaar
 * Tests for the SQLite cache meta data handler
 */
class FileTest extends TestCase
{
    /**
     * Default system timezone.
     *
     * @var string
     */
    private $timezone = null;

    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'bzr' ) )
        {
            $this->markTestSkipped( 'Bazaar binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );

        // Store default timezone
        $this->timezone = ini_get( 'date.timezone' );

        // Test data uses US/Mountain
        ini_set( 'date.timezone', 'US/Mountain' );
    }

    public function tearDown()
    {
        // Restore system timezone
        ini_set( 'date.timezone', $this->timezone );
 
        parent::tearDown();
    }

    public function testGetVersionString()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            "2",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            array(
                "1",
                "2",
            ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            'Richard Bateman <taxilian@gmail.com>',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            'richard <richard@shaoden>',
            $file->getAuthor( '1' )
        );
    }

    public function testGetAuthorInvalidVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        try {
            $file->getAuthor( 'invalid' );
            $this->fail( 'Expected \vcsNoSuchVersionException.' );
        } catch ( \vcsNoSuchVersionException $e )
        { /* Expected */ }
    }

    public function testGetLog()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $file->getLog();
        
        $this->assertEquals(
            array(
                "1" => new \vcsLogEntry(
                    "1", "richard <richard@shaoden>", "Initial commit", 1276559935
                    ),
                "2" => new \vcsLogEntry(
                    "2", "Richard Bateman <taxilian@gmail.com>", "Second commit", 1276563712
                    ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            new \vcsLogEntry(
                    "1", "richard <richard@shaoden>", "Initial commit", 1276559935
            ),
            $file->getLogEntry( "1" )
        );
    }

    public function testGetUnknownLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        try {
            $file->getLogEntry( "no_such_version" );
            $this->fail( 'Expected \vcsNoSuchVersionException.' );
        } catch ( \vcsNoSuchVersionException $e )
        { /* Expected */ }
    }

    public function testGetFileContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some other test file\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileBlame()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new \vcsBlameStruct(
                    'Some test file',
                    "1",
                    'richard@shaoden',
                    1276495200
                ),
                new \vcsBlameStruct(
                    'Another line in the file',
                    "1",
                    'richard@shaoden',
                    1276495200
                ),
                new \vcsBlameStruct(
                    "Added a new line",
                    "2",
                    "taxilian@gmail.com",
                    1276495200
                ),
            ),
            $file->blame()
        );
    }

    public function testGetFileBlameInvalidVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        try {
            $file->blame( "no_such_version" );
            $this->fail( 'Expected \vcsNoSuchVersionException.' );
        } catch ( \vcsNoSuchVersionException $e )
        { /* Expected */ }
    }

    public function testGetFileDiff()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        $diff = $file->getDiff( "1", "2" );
        
        $this->assertEquals(
            array(
                new Chunk(
                    1, 2, 1, 3,
                    array(
                        new Line( 3, 'Some test file' ),
                        new Line( 3, "Another line in the file" ),
                        new Line( 1, 'Added a new line' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }

    public function testGetFileDiffUnknownRevision()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );
        $file = new File( $this->tempDir, '/file' );

        try {
            $diff = $file->getDiff( "8" );
            $this->fail( 'Expected \vcsNoSuchVersionException.' );
        } catch ( \vcsNoSuchVersionException $e )
        { /* Expected */ }
    }
}

