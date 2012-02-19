<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\ZipArchive;

use \Vcs\TestCase;

/**
 * Tests for the SQLite cache meta data handler
 */
class CheckoutTest extends TestCase
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
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( 'file:///hopefully/not/existing/svn/repo' );
    }

    /**
     * @return void
     * @expectedException \vcsInvalidZipArchiveException
     */
    public function testInitializeInvalidArchive()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( __FILE__ );
    }

    public function testInitializeCheckout()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckout()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertFalse( $repository->update(), "There are never updates available for archive checkouts." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testIterateCheckoutContents()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
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
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
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

    public function testGetInvalid()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

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
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertEquals(
            $repository->get( '/dir1' ),
            new \vcsArchiveDirectory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $this->assertEquals(
            $repository->get( '/file' ),
            new \vcsArchiveFile( $this->tempDir, '/file' )
        );
    }
}

