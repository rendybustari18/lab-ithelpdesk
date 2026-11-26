<?php

use App\Libraries\AttachmentPolicy;
use CodeIgniter\Test\CIUnitTestCase;

final class AttachmentPolicyTest extends CIUnitTestCase
{
    public function testContentMustMatchAllowedImageOrPdfExtension(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'helpdesk-upload-');
        try {
            file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jZA8AAAAASUVORK5CYII='));
            self::assertTrue(AttachmentPolicy::validContent($path, 'png'));
            self::assertFalse(AttachmentPolicy::validContent($path, 'jpg'));
            self::assertFalse(AttachmentPolicy::validContent($path, 'exe'));
            file_put_contents($path, "%PDF-1.4\nexample\n%%EOF\n");
            self::assertTrue(AttachmentPolicy::validContent($path, 'pdf'));
        } finally {
            unlink($path);
        }
    }
}
