<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\SvnCli;

use \Vcs\TestCase;

use \Vcs\Blame;
use \Vcs\Cache;
use \Vcs\LogEntry;
use \Vcs\Diff\Chunk;
use \Vcs\Diff\Line;

/**
 * Tests for the SQLite cache meta data handler
 */
class FileTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'svn' ) )
        {
            $this->markTestSkipped( 'Svn binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        Cache::initialize( $this->createTempDir() );
    }

    public function testGetVersionString()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            "5",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            array( "1", "5" ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor( '1' )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetAuthorInvalidVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getAuthor( 'invalid' );
    }

    public function testGetLog()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                1 => new LogEntry(
                    '1',
                    'kore',
                    "- Added test file\n",
                    1226412609
                ),
                5 => new LogEntry(
                    '5',
                    'kore',
                    "- Added another line to file\n",
                    1226595170
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            new LogEntry(
                '1',
                'kore',
                "- Added test file\n",
                1226412609
            ),
            $file->getLogEntry( "1" )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetUnknownLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getLogEntry( "no_such_version" );
    }

    public function testGetFileContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some test contents\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileVersionedFileContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            "Some test file\n",
            $file->getVersionedContent( "1" )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetFileContentsInvalidVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getVersionedContent( "no_such_version" );
    }

    public function testGetFileBlame()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new Blame(
                    'Some test file',
                    '1',
                    'kore',
                    1226412609
                ),
                new Blame(
                    'A second line, in a later revision',
                    '5',
                    'kore',
                    1226595170
                ),
            ),
            $file->blame()
        );
    }

    public function testGetBinaryFileBlame()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/binary' );

        $this->assertEquals(
            false,
            $file->blame()
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetFileBlameInvalidVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->blame( "no_such_version" );
    }

    public function testGetFileDiff()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $file = new File( $this->tempDir, '/file' );

        $diff = $file->getDiff( 1 );
        

        $this->assertEquals(
            '/file',
            $diff[0]->from
        );
        $this->assertEquals(
            '/file',
            $diff[0]->to
        );
        $this->assertEquals(
            array(
                new Chunk(
                    1, 1, 1, 2,
                    array(
                        new Line( 3, 'Some test file' ),
                        new Line( 1, 'A second line, in a later revision' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }
}

