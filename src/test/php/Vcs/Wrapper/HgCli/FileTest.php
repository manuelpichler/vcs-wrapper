<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision: 955 $
 * @license GPLv3
 */
namespace Vcs\Wrapper\HgCli;

use \Vcs\TestCase;

use \Vcs\Blame;
use \Vcs\Cache;
use \Vcs\LogEntry;
use \Vcs\Diff\Chunk;
use \Vcs\Diff\Line;

/**
 * @group mercurial
 * Tests for the SQLite cache meta data handler
 */
class FileTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'hg' ) )
        {
            $this->markTestSkipped( 'Mercurial binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        Cache::initialize( $this->createTempDir() );
    }

    public function testGetVersionString()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            "b8ec741c8de1e60c5fedd98c350e3569c46ed630",
            $file->getVersionString()
        );
    }

    public function testGetVersions()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertSame(
            array(
                "9923e3bfe735ad54d67c38351400097e25aadabd",
                "b8ec741c8de1e60c5fedd98c350e3569c46ed630",
            ),
            $file->getVersions()
        );
    }

    public function testGetAuthor()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            't.tom',
            $file->getAuthor()
        );
    }

    public function testGetAuthorOldVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            't.tom',
            $file->getAuthor( 'b8ec741c8de1e60c5fedd98c350e3569c46ed630' )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetAuthorInvalidVersion()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getAuthor( 'invalid' );
    }

    public function testGetLog()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                '9923e3bfe735ad54d67c38351400097e25aadabd' => new LogEntry(
                    "9923e3bfe735ad54d67c38351400097e25aadabd", "t.tom", "- Added a first test file", 1263330480
                ),
                'b8ec741c8de1e60c5fedd98c350e3569c46ed630' => new LogEntry(
                    "b8ec741c8de1e60c5fedd98c350e3569c46ed630", "t.tom", "- Modified file", 1263330660
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            new LogEntry(
                    "b8ec741c8de1e60c5fedd98c350e3569c46ed630", "t.tom", "- Modified file", 1263330660
            ),
            $file->getLogEntry( "b8ec741c8de1e60c5fedd98c350e3569c46ed630" )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetUnknownLogEntry()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getLogEntry( "no_such_version" );
    }

    public function testGetFileContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some other test file\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetFileBlame()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals(
            array(
                new Blame(
                    'Some test file',
                    '9923e3bfe735ad54d67c38351400097e25aadabd',
                    't.tom',
                    1263330521
                ),
                new Blame(
                    'Another line in the file',
                    'b8ec741c8de1e60c5fedd98c350e3569c46ed630',
                    't.tom',
                    1263330677
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
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->blame( "no_such_version" );
    }

    public function testGetFileDiff()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );
        $file = new File( $this->tempDir, '/file' );

        $diff = $file->getDiff( "9923e3bfe735ad54d67c38351400097e25aadabd" );
        
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
        $repository->initialize( 'file://' . $this->extractRepository( 'hg' ) );

        $file = new File( $this->tempDir, '/file' );
        $file->getDiff( "1" );
    }
}

