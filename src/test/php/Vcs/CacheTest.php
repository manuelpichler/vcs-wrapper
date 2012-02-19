<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs;

use \Vcs\TestCase;

/**
 * Tests for the SQLite cache meta data handler
 */
class CacheTest extends TestCase
{
    /**
     * @return void
     * @expectedException \Vcs\Cache\NotInitializedException
     */
    public function testCacheNotInitialized()
    {
        Cache::get( '/foo', 1, 'data' );
    }

    public function testValueNotInCache()
    {
        Cache::initialize( $this->tempDir, 100, .8 );
        $this->assertFalse(
            Cache::get( '/foo', 1, 'data' ),
            'Expected false, because item should not be in cache.'
        );
    }

    public function testCacheScalarValues()
    {
        $values = array( 1, .1, 'foo', true );
        Cache::initialize( $this->tempDir, 100, .8 );

        foreach ( $values as $nr => $value )
        {
            Cache::cache( '/foo', (string) $nr, 'data', $value );
        }

        foreach ( $values as $nr => $value )
        {
            $this->assertSame(
                $value,
                Cache::get( '/foo', $nr, 'data' ),
                'Wrong item returned from cache'
            );
        }
    }

    public function testCacheArray()
    {
        $values = array( 1, .1, 'foo', true );
        Cache::initialize( $this->tempDir, 100, .8 );
        Cache::cache( '/foo', '1', 'data', $values );

        $this->assertSame(
            $values,
            Cache::get( '/foo', '1', 'data' ),
            'Wrong item returned from cache'
        );
    }

    /**
     * @return void
     * @expectedException \Vcs\Cache\NotCacheableException
     */
    public function testInvalidCacheItem()
    {
        Cache::initialize( $this->tempDir, 100, .8 );
        Cache::cache( '/foo', '1', 'data', $this );
    }

    public function testCacheCacheableObject()
    {
        Cache::initialize( $this->tempDir, 100, .8 );
        Cache::cache( '/foo', '1', 'data', $object = new vcsTestCacheableObject( 'foo' ) );

        $this->assertEquals(
            $object,
            Cache::get( '/foo', '1', 'data' ),
            'Wrong item returned from cache'
        );
    }

    public function testPurgeOldCacheEntries()
    {
        $values = array( 1, .1, 'foo', true );
        Cache::initialize( $this->tempDir, 50, .8 );

        foreach ( $values as $nr => $value )
        {
            Cache::cache( '/foo', (string) $nr, 'data', $value );
        }
        Cache::forceCleanup();

        $this->assertFalse(
            Cache::get( '/foo', 0, 'data' ),
            'Item 0 is not expected to be in the cache anymore.'
        );
        $this->assertFalse(
            Cache::get( '/foo', 1, 'data' ),
            'Item 1 is not expected to be in the cache anymore.'
        );
        $this->assertFalse(
            Cache::get( '/foo', 2, 'data' ),
            'Item 2 is not expected to be in the cache anymore.'
        );
        $this->assertTrue(
            Cache::get( '/foo', 3, 'data' ),
            'Item 3 is still expected to be in the cache.'
        );
    }
}

class vcsTestCacheableObject implements \Vcs\Cache\Cacheable
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
