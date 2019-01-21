<?php
namespace Ottosmops\Hash\Test;

use Ottosmops\Hash\Hash;

use Ottosmops\Hash\Exceptions\FileNotFound;
use Ottosmops\Hash\Exceptions\SeparatorNotFound;

use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{
    protected $valide_dir = __DIR__.'/testfiles/valide/';

    protected $non_valide_dir = __DIR__.'/testfiles/non-valide/';

    protected $valide_dir_binary = __DIR__.'/testfiles/valide-binary/';

    protected $file_not_in_manifest = __DIR__.'/testfiles/file-not-in-manifest/';

    protected $file_not_in_dir = __DIR__.'/testfiles/file-not-in-dir/';

    protected $temp = __DIR__.'/testfiles/temp/';

    /** @test */
    public function it_can_valdiate_a_valide_manifest()
    {
        $manifest = $this->valide_dir.'manifest';
        $hash = New Hash();
        $this->assertTrue($hash->verifyManifest($manifest));
    }

    /** @test */
    public function it_can_valdiate_a_valide_sh1_manifest()
    {
        $manifest = 'manifest-sha1';
        $hash = New Hash('sha1');
        $hash->createManifest($this->temp, $manifest);
        $this->assertTrue($hash->verifyManifest($this->temp . $manifest));
        unlink($this->temp . $manifest);
    }

     /** @test */
    public function it_can_valdiate_a_valide_manifest_binary()
    {
        $manifest = $this->valide_dir_binary.'manifest';
        $hash = New Hash();
        $this->assertTrue($hash->verifyManifest($manifest));
    }

    /** @test */
    public function it_can_recognize_file_not_in_dir()
    {
        $manifest = $this->file_not_in_dir.'manifest';
        $hash = New Hash();
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
        $hash = New Hash();
        $hash->createManifest($this->temp);
        $this->assertTrue($hash->verifyManifest($this->temp .'manifest'));
        unlink ($this->temp .'manifest');
    }

    /** @test */
    public function it_can_create_manifest_with_name()
    {
        $hash = New Hash();
        $hash->createManifest($this->temp, 'name');
        $this->assertTrue($hash->verifyManifest($this->temp .'name'));
        unlink ($this->temp .'name');
    }

    /** @test */
    public function it_can_create_manifest_flat()
    {
        $hash = New Hash();
        $this->assertSame($hash->createManifest($this->temp, 'manifest', false), 2);
        $this->assertTrue($hash->verifyManifest($this->temp .'manifest'));
        unlink ($this->temp .'manifest');
    }

    /** @test */
    public function it_recoginzes_corrupted_files()
    {
        $manifest = $this->non_valide_dir.'manifest';
        $hash = New Hash();
        $this->assertFalse($hash->verifyManifest($manifest));
        $this->assertSame($hash->messages[1], 'error line 1: could not verify line d41d8cd98f00b204e9800998ecf8427e  test');
    }
}
