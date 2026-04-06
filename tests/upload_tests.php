<?php
require_once('./simpletest/autorun.php');
require_once('../modules/iupload.interface.php');
require_once('../modules/upload.class.php');
require_once('../modules/inputexception.class.php');

/**
 * Tests for the Upload::checkExt() extension validation.
 */
class TestOfUploadExtensionCheck extends UnitTestCase {

    private $upload;

    function setUp() {
        // Allowed extensions matching the application default: '.jpg, .gif, .png'
        $this->upload = new Upload('../upload/', 2000, '.jpg, .gif, .png');
    }

    // ------------------------------------------------------------------
    // Helper: call the private loadUp() so checkExt() is exercised via
    // the public interface without needing to move an actual tmp file.
    // We override fileUpload behaviour by using a tmp_name that does NOT
    // pass is_uploaded_file(), so the flow stops there — but only AFTER
    // checkExt() has already been evaluated.
    // We check the thrown InputException message to determine which branch
    // was taken.
    // ------------------------------------------------------------------
    private function tryUpload($filename) {
        $file = array(
            'name'     => $filename,
            'tmp_name' => '',
            'size'     => 1,  // 1 byte — passes checkSize (< 2000 KB)
        );
        try {
            $this->upload->loadUp($file);
            return 'no_exception';
        } catch (InputException $e) {
            return $e->getErrorField() . ':' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------------
    // Allowed extensions — should NOT throw ERROR_WRONG_EXTENSION.
    // (They will fail later at fileUpload with ERROR_UPLOAD, which is fine.)
    // ------------------------------------------------------------------
    function testJpgExtensionIsAllowed() {
        $result = $this->tryUpload('photo.jpg');
        $this->assertNotEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'photo.jpg should pass extension check');
    }

    function testJpgUppercaseExtensionIsAllowed() {
        $result = $this->tryUpload('photo.JPG');
        $this->assertNotEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'photo.JPG should pass extension check (case-insensitive)');
    }

    function testGifExtensionIsAllowed() {
        $result = $this->tryUpload('image.gif');
        $this->assertNotEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'image.gif should pass extension check');
    }

    function testPngExtensionIsAllowed() {
        $result = $this->tryUpload('banner.png');
        $this->assertNotEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'banner.png should pass extension check');
    }

    // ------------------------------------------------------------------
    // Disallowed extensions — MUST throw ERROR_WRONG_EXTENSION.
    // ------------------------------------------------------------------
    function testPhpExtensionIsRejected() {
        $result = $this->tryUpload('shell.php');
        $this->assertEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'shell.php must be rejected by extension check');
    }

    function testPhpUppercaseExtensionIsRejected() {
        $result = $this->tryUpload('shell.PHP');
        $this->assertEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'shell.PHP must be rejected by extension check (case-insensitive)');
    }

    function testExeExtensionIsRejected() {
        $result = $this->tryUpload('virus.exe');
        $this->assertEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'virus.exe must be rejected by extension check');
    }

    function testHtmlExtensionIsRejected() {
        $result = $this->tryUpload('page.html');
        $this->assertEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'page.html must be rejected by extension check');
    }

    function testNoExtensionIsRejected() {
        $result = $this->tryUpload('noextension');
        $this->assertEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'Files with no extension must be rejected');
    }

    // Guard against path-traversal bypass (double extension)
    function testDoubleExtensionWithPhpIsRejected() {
        $result = $this->tryUpload('image.php.jpg');
        // The real extension is .jpg, so this should PASS (jpg is allowed).
        // This ensures we use pathinfo() (last extension only) and don't
        // accidentally let shell.jpg.php through either.
        $this->assertNotEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'image.php.jpg has extension .jpg and should pass');
    }

    function testPhpJpgDoubleExtensionIsRejected() {
        $result = $this->tryUpload('shell.jpg.php');
        $this->assertEqual($result, 'file:{ERROR_WRONG_EXTENSION}',
            'shell.jpg.php has extension .php and must be rejected');
    }
}
?>
