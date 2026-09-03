/**
 * build.js  (npm run build)
 *
 * Assembles the shippable package from src/ into dist/content-qa/ — a clean,
 * deterministic copy, no compilation. The release workflow zips dist/content-qa
 * with the package name as the zip's single top-level folder, so consumers
 * unzip it straight into their vendor directory.
 *
 * Usage: node scripts/build.js
 */

'use strict';

/**
 * Define fs, path
 * Node's filesystem and path helpers.
 */
const fs = require( 'fs' );
const path = require( 'path' );

/**
 * Define ROOT, SRC, DIST
 * The repo root, the source directory, and the named output directory.
 */
const ROOT = path.resolve( __dirname, '..' );
const SRC = path.join( ROOT, 'src' );
const DIST = path.join( ROOT, 'dist', 'content-qa' );

/**
 * Check if the source directory exists.
 */
if ( ! fs.existsSync( SRC ) ) {
	console.error( 'build: src/ not found — nothing to assemble.' );
	process.exit( 1 );
}

/**
 * Clear any previous build so removed files never linger.
 */
fs.rmSync( path.join( ROOT, 'dist' ), { recursive: true, force: true } );

/**
 * Copy the package verbatim into dist/content-qa/.
 */
fs.cpSync( SRC, DIST, { recursive: true } );

/**
 * Define fileCount
 * How many files landed in the output, for the summary line.
 */
const fileCount = fs
	.readdirSync( DIST, { recursive: true } )
	.filter( ( entry ) => fs.statSync( path.join( DIST, entry ) ).isFile() ).length;

console.log( `build: assembled dist/content-qa/ from src/ (${ fileCount } files).` );
