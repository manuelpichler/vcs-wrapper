<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Diff;

use \Vcs\TestCase;

/**
 * Tests for the unified diff parser
 */
class UnifiedDiffParserTest extends TestCase
{
    public static function getUnifiedDiffFiles()
    {
        $diffs = array();
        $files = glob( __DIR__ . '/../../../resources/diff/unified/s_*.diff' );
        foreach ( $files as $file )
        {
            $diffs[] = array(
                $file,
                substr( $file, 0, -4 ) . 'php'
            );
        }

        return $diffs;
    }

    /**
     * @dataProvider getUnifiedDiffFiles
     */
    public function testParseUnifiedDiff( $from, $to )
    {
        if ( !is_file( $to ) )
        {
            $this->markTestIncomplete( "Comparision file $to does not yet exist." );
        }

        $parser = new \vcsUnifiedDiffParser();
        $diff = $parser->parseFile( $from );

        // Store diff result in temp folder for manual check in case of failure
        file_put_contents( $this->tempDir . '/' . basename( $to ), "<?php\n\n return " . var_export( $diff, true ) . ";\n\n" );

        // Compare parsed diff against expected diff.
        $this->assertEquals(
            include $to,
            $diff,
            "Diff for file $from does not match expectations."
        );
    }
}

