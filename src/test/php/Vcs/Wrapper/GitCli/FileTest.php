<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\GitCli;

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

        if ( false === $this->hasBinary( 'git' ) )
        {
            $this->markTestSkipped( 'Git binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        Cache::initialize( $this->createTempDir() );
    }

    public function testGetVersionString()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            array(
                "43fb423f4ee079af2f3cba4e07eb8b10f4476815",
                "2037a8d0efd4e51a4dd84161837f8865cf7d34b1",
            ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            'kore',
            $file->getAuthor( '2037a8d0efd4e51a4dd84161837f8865cf7d34b1' )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetAuthorInvalidVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getAuthor( 'invalid' );
    }

    public function testGetLog()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                '43fb423f4ee079af2f3cba4e07eb8b10f4476815' => new LogEntry(
                    "43fb423f4ee079af2f3cba4e07eb8b10f4476815", "kore", "- Added a first test file\n", 1226920616
                ),
                '2037a8d0efd4e51a4dd84161837f8865cf7d34b1' => new LogEntry(
                    "2037a8d0efd4e51a4dd84161837f8865cf7d34b1", "kore", "- Modified file\n", 1226921232
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            new LogEntry(
                    "2037a8d0efd4e51a4dd84161837f8865cf7d34b1", "kore", "- Modified file\n", 1226921232
            ),
            $file->getLogEntry( "2037a8d0efd4e51a4dd84161837f8865cf7d34b1" )
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

        $file = new File( $this->tempDir, '/file' );
        $file->getLogEntry( "no_such_version" );
    }

    public function testGetFileContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some other test file\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileBlame()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new Blame(
                    'Some test file',
                    '43fb423f4ee079af2f3cba4e07eb8b10f447681',
                    'kore',
                    1226920616
                ),
                new Blame(
                    'Another line in the file',
                    '2037a8d0efd4e51a4dd84161837f8865cf7d34b1',
                    'kore',
                    1226921232
                ),
            ),
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
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->blame( "no_such_version" );
    }

    public function testGetFileDiff()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );
        $file = new File( $this->tempDir, '/file' );

        $diff = $file->getDiff( "43fb423f4ee079af2f3cba4e07eb8b10f4476815" );
        
        $this->assertEquals(
            array(
                new Chunk(
                    1, 1, 1, 2,
                    array(
                        new Line( 3, 'Some test file' ),
                        new Line( 1, 'Another line in the file' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetFileDiffUnknownRevision()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'git' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getDiff( "1" );
    }
}

