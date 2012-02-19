<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Cache;

use \Vcs\TestCase;

/**
 * Tests for the SQLite cache meta data handler
 */
class CacheTest extends TestCase
{
    /**
     * @return void
     * @expectedException \vcsCacheNotInitializedException
     */
    public function testCacheNotInitialized()
    {
        \vcsCache::get( '/foo', 1, 'data' );
    }

    public function testValueNotInCache()
    {
        \vcsCache::initialize( $this->tempDir, 100, .8 );
        $this->assertFalse(
            \vcsCache::get( '/foo', 1, 'data' ),
            'Expected false, because item should not be in cache.'
        );
    }

    public function testCacheScalarValues()
    {
        $values = array( 1, .1, 'foo', true );
        \vcsCache::initialize( $this->tempDir, 100, .8 );

        foreach ( $values as $nr => $value )
        {
            \vcsCache::cache( '/foo', (string) $nr, 'data', $value );
        }

        foreach ( $values as $nr => $value )
        {
            $this->assertSame(
                $value,
                \vcsCache::get( '/foo', $nr, 'data' ),
                'Wrong item returned from cache'
            );
        }
    }

    public function testCacheArray()
    {
        $values = array( 1, .1, 'foo', true );
        \vcsCache::initialize( $this->tempDir, 100, .8 );
        \vcsCache::cache( '/foo', '1', 'data', $values );

        $this->assertSame(
            $values,
            \vcsCache::get( '/foo', '1', 'data' ),
            'Wrong item returned from cache'
        );
    }

    /**
     * @return void
     * @expectedException \vcsNotCacheableException
     */
    public function testInvalidCacheItem()
    {
        \vcsCache::initialize( $this->tempDir, 100, .8 );
        \vcsCache::cache( '/foo', '1', 'data', $this );
    }

    public function testCacheCacheableObject()
    {
        \vcsCache::initialize( $this->tempDir, 100, .8 );
        \vcsCache::cache( '/foo', '1', 'data', $object = new vcsTestCacheableObject( 'foo' ) );

        $this->assertEquals(
            $object,
            \vcsCache::get( '/foo', '1', 'data' ),
            'Wrong item returned from cache'
        );
    }

    public function testPurgeOldCacheEntries()
    {
        $values = array( 1, .1, 'foo', true );
        \vcsCache::initialize( $this->tempDir, 50, .8 );

        foreach ( $values as $nr => $value )
        {
            \vcsCache::cache( '/foo', (string) $nr, 'data', $value );
        }
        \vcsCache::forceCleanup();

        $this->assertFalse(
            \vcsCache::get( '/foo', 0, 'data' ),
            'Item 0 is not expected to be in the cache anymore.'
        );
        $this->assertFalse(
            \vcsCache::get( '/foo', 1, 'data' ),
            'Item 1 is not expected to be in the cache anymore.'
        );
        $this->assertFalse(
            \vcsCache::get( '/foo', 2, 'data' ),
            'Item 2 is not expected to be in the cache anymore.'
        );
        $this->assertTrue(
            \vcsCache::get( '/foo', 3, 'data' ),
            'Item 3 is still expected to be in the cache.'
        );
    }
}

class vcsTestCacheableObject implements \arbitCacheable
{
    public $foo = null;
    public function __construct( $foo )
    {
        $this->foo = $foo;
    }
    public static function __set_state( array $properties )
    {
        return new vcsTestCacheableObject( reset( $properties ) );
    }
}
