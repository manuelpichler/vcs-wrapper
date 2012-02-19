<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\CvsCli;

use \Vcs\TestCase;

/**
 * Tests for the CVS cli wrapper checkout implementation.
 */
class CheckoutTest extends TestCase
{
    /**
     * Initializes the the meta data cache used by the CVS wrapper.
     */
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'cvs' ) )
        {
            $this->markTestSkipped( 'CVS binary not found.' );
        }

        // Create a cache, required for all CVS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );
    }

    /**
     * @return void
     * @expectedException \pbsSystemProcessNonZeroExitCodeException
     */
    public function testInitializeInvalidCheckout()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( '/hopefully/not/existing/cvs#repo' );
    }

    public function testInitializeCheckout()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testInitializeCheckoutWithVersion()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs#1.2' );

        $this->assertFileExists( $this->tempDir . '/file' );
        $this->assertFileExists( $this->tempDir . '/dir1/file' );
        $this->assertFileNotExists( $this->tempDir . '/dir1/file1' );
    }

    public function testUpdateCheckout()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $this->assertFalse( $checkout->update(), "Repository should already be on latest revision." );

        $this->assertTrue(
            file_exists( $this->tempDir . '/file' ),
            'Expected file "/file" in checkout.'
        );
    }

    public function testUpdateCheckoutWithUpdate()
    {
        // Create a repository copy
        $dataDir = $this->extractRepository( 'cvs' );
        $repoDir = $this->createTempDir() . '/cvs';

        self::copyRecursive( $dataDir, $repoDir );

        // Create a clean checkout of the cloned repository
        $checkin = new \vcsCvsCliCheckout( $this->tempDir . '/in' );
        $checkin->initialize( $repoDir . '#cvs' );

        $checkout = new \vcsCvsCliCheckout( $this->tempDir . '/out' );
        $checkout->initialize( $repoDir . '#cvs' );

        // Manually add a new file
        file_put_contents( $this->tempDir . '/in/foo.txt', 'Foobar Bar Foo' );

        // Add file to repository
        $add = new \vcsCvsCliProcess();
        $add->workingDirectory( $this->tempDir . '/in' )
            ->argument( 'add' )
            ->argument( 'foo.txt' )
            ->execute();

        $commit = new \vcsCvsCliProcess();
        $commit->workingDirectory( $this->tempDir . '/in' )
               ->argument( 'commit' )
               ->argument( '-m' )
               ->argument( 'Test commit...' )
               ->execute();

        // No update, actual working copy
        $this->assertFalse( $checkin->update() );

        $this->assertFileNotExists( $this->tempDir . '/out/foo.txt' );
        $this->assertTrue( $checkout->update() );
        $this->assertFileExists( $this->tempDir . '/out/foo.txt' );
    }

    public function testUpdateCheckoutToOldVersion()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );
        $this->assertFileExists( $this->tempDir . '/dir1/file', 'Expected file "/dir1/file" in checkout.' );

        $checkout->update( '1.0' );
        $this->assertFileNotExists( $this->tempDir . '/dir1/file', 'Expected file "/dir1/file" not in checkout.' );
    }

    public function testUpdateCheckoutFromTagToHead()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs#milestone' );

        $this->assertFileNotExists( $this->tempDir . '/dir1/file1', 'Expected file "/dir1/file1" not in checkout.' );
        $checkout->update( 'HEAD' );
        $this->assertFileExists( $this->tempDir . '/dir1/file1', 'Expected file "/dir1/file1" in checkout.' );
    }

    public function testGetCheckout()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs#milestone' );

        $this->assertSame(
            $checkout->get(),
            $checkout
        );

        $this->assertSame(
            $checkout->get( '/' ),
            $checkout
        );
    }

    public function testGetInvalid()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs#milestone' );

        try
        {
            $checkout->get( '/../' );
            $this->fail( 'Expected \vcsFileNotFoundException.' );
        }
        catch ( \vcsFileNotFoundException $e )
        { /* Expected */ }
    }

    public function testGetDirectory()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs#milestone' );

        $this->assertEquals(
            $checkout->get( '/dir1' ),
            new \vcsCvsCliDirectory( $this->tempDir, '/dir1' )
        );
    }

    public function testGetFile()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs#milestone' );

        $this->assertEquals(
            $checkout->get( '/file' ),
            new \vcsCvsCliFile( $this->tempDir, '/file' )
        );
    }
}
