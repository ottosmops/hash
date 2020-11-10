<?php
namespace Ottosmops\Hash\Test;

use Ottosmops\Hash\Hash;

use Ottosmops\Hash\Exceptions\FileNotFound;

use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{
    protected $validate_dir = __DIR__.'/testfiles/validate/';

    protected $non_validate_dir = __DIR__.'/testfiles/non-validate/';

    protected $validate_dir_binary = __DIR__.'/testfiles/validate-binary/';

    protected $file_not_in_manifest = __DIR__.'/testfiles/file-not-in-manifest/';

    protected $file_not_in_dir = __DIR__.'/testfiles/file-not-in-dir/';

    protected $temp = __DIR__.'/testfiles/temp/';

    /** @test */
    public function it_can_validate_a_validate_manifest()
    {
        $manifest = $this->validate_dir.'manifest';
        $hash = new Hash();
        $this->assertTrue($hash->verifyManifest($manifest));
    }

    /** @test */
    public function it_can_validate_a_validate_sh1_manifest()
    {
        $manifest = 'manifest-sha1';
        $hash = new Hash('sha1');
        $hash->createManifest($this->temp, $manifest);
        $this->assertTrue($hash->verifyManifest($this->temp . $manifest));
        unlink($this->temp . $manifest);
    }

     /** @test */
    public function it_can_validate_a_validate_manifest_binary()
    {
        $manifest = $this->validate_dir_binary.'manifest';
        $hash = new Hash();
        $this->assertTrue($hash->verifyManifest($manifest));
    }

    /** @test */
    public function it_can_recognize_file_not_in_dir()
    {
        $manifest = $this->file_not_in_dir.'manifest';
        $hash = new Hash();
        $this->assertFalse($hash->verifyManifest($manifest));
        $this->assertSame($hash->messages[1], 'error line 1: could not find test');
    }

    /** @test */
    public function it_will_throw_an_exception_when_file_is_not_found()
    {
        $this->expectException(FileNotFound::class);
        $text = (new Hash())->verifyManifest('/no/manifest/here/manifest');
    }

    /** @test */
    public function it_can_create_a_deep_md5sums()
    {
        $hash = new Hash();
        $hash->createManifest($this->temp);
        $this->assertTrue($hash->verifyManifest($this->temp .'manifest-md5.txt'));
        unlink($this->temp .'manifest-md5.txt');
    }

    /** @test */
    public function it_can_create_manifest_with_name()
    {
        $hash = new Hash();
        $hash->createManifest($this->temp, 'name');
        $this->assertTrue($hash->verifyManifest($this->temp .'name'));
        unlink($this->temp .'name');
    }

    /** @test */
    public function it_can_create_manifest_flat()
    {
        $hash = new Hash();
        $this->assertSame($hash->createManifest($this->temp, 'manifest-md5.txt', false), 2);
        $this->assertTrue($hash->verifyManifest($this->temp .'manifest-md5.txt'));
        unlink($this->temp .'manifest-md5.txt');
    }

    /** @test */
    public function it_recognizes_corrupted_files()
    {
        $manifest = $this->non_validate_dir.'manifest';
        $hash = new Hash();
        $this->assertFalse($hash->verifyManifest($manifest));
        $this->assertSame($hash->messages[1], 'error line 1: could not verify line d41d8cd98f00b204e9800998ecf8427e  test');
    }

    /** @test */
    public function it_finds_a_hash()
    {
        $manifest = $this->validate_dir . 'manifest';
        $hash = new Hash();
        $search = 'ad0234829205b9033196ba818f7a872b';
        $expected = 'test2';
        $actual = $hash->manifestContainsHash($manifest, $search);
        $this->assertEquals($expected, $actual);
        $expected = 'false';
        $search = 'bd0234829205b9033196ba818f7a872b';
        $actual = $hash->manifestContainsHash($manifest, $search);
        $this->assertFalse($actual);
    }
}
