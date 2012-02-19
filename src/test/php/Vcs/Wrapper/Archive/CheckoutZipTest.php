<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\Archive;

use \Vcs\TestCase;

use \Vcs\Wrapper\Archive\File;
use \Vcs\Wrapper\Archive\Directory;
use \Vcs\Wrapper\Archive\Checkout\Zip;

/**
 * Tests for the SQLite cache meta data handler
 */
class CheckoutZipTest extends TestCase
{
    protected function setUp()
    {
        if ( !class_exists( 'ZipArchive' ) )
        {
            $this->markTestSkipped( 'Compile PHP with --enable-zip to get support for zip archive handling.' );
        }

        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );
    }

    /**
     * @return void
     * @expectedException \vcsNoSuchFileException
     */
    public function testInitializeInvalidCheckout()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( 'file:///hopefully/not/existing/svn/repo' );
    }

    /**
     * @return void
     * @expectedException \vcsInvalidZipArchiveException
     */
    public function testInitializeInvalidArchive()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( __FILE__ );
    }

    public function testInitializeCheckout()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertFalse( $repository->update(), "There are never updates available for archive checkouts." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testIterateCheckoutContents()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

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
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

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
     * @expectedException \vcsFileNotFoundException
     */
    public function testGetInvalid()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );
        $repository->get( '/../' );
    }

    public function testGetDirectory()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new Directory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertEquals(
            $repository->get( '/file' ),
            new File( $this->tempDir, '/file' )
        );
    }
}

