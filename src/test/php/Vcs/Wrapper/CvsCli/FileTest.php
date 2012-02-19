<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\CvsCli;

use \Vcs\TestCase;

use \Vcs\Blame;
use \Vcs\Cache;
use \Vcs\LogEntry;
use \Vcs\Diff\Chunk;
use \Vcs\Diff\Line;

/**
 * Tests for the CVS Cli wrapper
 */
class FileTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'cvs' ) )
        {
            $this->markTestSkipped( 'CVS binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        Cache::initialize( $this->createTempDir() );
    }

    public function testGetVersionString()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $this->assertEquals( '1.2', $file->getVersionString() );

        $file = new File( $this->tempDir, '/dir1/file' );
        $this->assertEquals( '1.3', $file->getVersionString() );
    }

    public function testGetVersions()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $this->assertSame( array( '1.1', '1.2' ), $file->getVersions()  );

        $file = new File( $this->tempDir, '/dir1/file' );
        $this->assertSame( array( '1.1', '1.2', '1.3' ), $file->getVersions()  );
    }

    public function testCompareVersions()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );
        $file = new File( $this->tempDir, '/file' );

        $this->assertEquals( 0, $file->compareVersions( '1.1', '1.1' ) );
        $this->assertLessThan( 0, $file->compareVersions( '1.1', '1.2' ) );
        $this->assertGreaterThan( 0, $file->compareVersions( '1.3', '1.2' ) );
    }

    public function testGetAuthor()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $this->assertEquals( 'manu', $file->getAuthor() );
    }

    public function testGetAuthorWithVersion()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $this->assertEquals( 'manu', $file->getAuthor( '1.1' ) );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetAuthorWithInvalidVersion()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $file->getAuthor( '1.10' );
    }

    public function testGetLog()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            array(
                '1.1' => new LogEntry(
                    '1.1',
                    'manu',
                    '- Added file in subdir',
                    1227507833
                ),
                '1.2' => new LogEntry(
                    '1.2',
                    'manu',
                    '- A',
                    1227804262
                ),
                '1.3' => new LogEntry(
                    '1.3',
                    'manu',
                    '- Test file modified.',
                    1227804446
                ),
            ),
            $file->getLog()
        );
    }

    public function testGetLogEntry()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $this->assertEquals(
            new LogEntry(
                '1.2',
                'manu',
                '- Added another line to file',
                1227507961
            ),
            $file->getLogEntry( '1.2' )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetUnknownLogEntry()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $file->getLogEntry( "no_such_version" );
    }

    public function testGetFileContents()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/dir1/file1' );
        $this->assertEquals( "Another test file\n", $file->getContents() );
    }

    public function testGetFileMimeType()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/dir1/file1' );
        $this->assertEquals( 'application/octet-stream', $file->getMimeType() );
    }

    public function testGetFileVersionedFileContents()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/dir1/file' );
        $this->assertEquals( "Some test contents\n", $file->getVersionedContent( '1.1' ) );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetFileContentsInvalidVersion()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/file' );
        $file->getVersionedContent( 'no_such_version' );
    }

    public function testGetFileBlame()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/dir1/file' );
        $this->assertEquals(
            array(
                new Blame(
                    'Some test contents',
                    '1.1',
                    'manu',
                    1227481200
                ),
                new Blame(
                    'More test contents',
                    '1.2',
                    'manu',
                    1227740400
                ),
                new Blame(
                    'And another test line',
                    '1.3',
                    'manu',
                    1227740400
                ),
            ),
            $file->blame()
        );
    }

    public function testGetFileBlameWithVersion()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/dir1/file' );
        $this->assertEquals(
            array(
                new Blame(
                    'Some test contents',
                    '1.1',
                    'manu',
                    1227481200
                ),
            ),
            $file->blame( '1.1' )
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\NoSuchVersionException
     */
    public function testGetFileBlameWithInvalidVersion()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/dir1/file' );
        $file->blame( 'no_such_version' );
    }

    public function testGetFileDiff()
    {
        $checkout = new Checkout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $file = new File( $this->tempDir, '/dir1/file' );
        $diff = $file->getDiff( '1.1' );

        $this->assertEquals(
            array(
                new Chunk(
                    1, 1, 1, 3,
                    array(
                        new Line( 3, 'Some test contents' ),
                        new Line( 1, 'More test contents' ),
                        new Line( 1, 'And another test line' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }
}
