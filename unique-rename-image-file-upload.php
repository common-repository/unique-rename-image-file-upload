<?php 
/*
Plugin Name: Unique Rename Image File Upload
Plugin URI: https://fumidzuki.com
Description: The upload image filename change to random unique filename. (target extension. bmp, gif, jpg, jpeg, png).
Version: 1.0.0
Author: fumidzuki
Author URI: https://fumidzuki.com
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * @version 1.0.0
 * @author fumidzuki
 * @copyright (c) 2018 fumidzuki
 * @license https://www.gnu.org/licenses/gpl-2.0.html
 */
namespace Fumidzuki;

if (!defined('ABSPATH')) {
  exit;
}
if (!class_exists(\Fumidzuki\UniqueRenameImageFileUpload::class)) {
  /**
   * 
   */
  class UniqueRenameImageFileUpload
  {
    const VERSION = "1.0.0";
    const FILE_EXTENSION = array('bmp', 'gif', 'jpg', 'jpeg', 'png');

    /**
     * コンストラクタ
     */
    function __construct()
    {
      add_filter('sanitize_file_name', array($this, 'sanitize_file_name'));
      add_filter('wp_insert_attachment_data', array($this, 'wp_insert_attachment_data'), 10, 2);
    }

    /**
     * アップロードしたファイル名称を変更する
     * 
     * アップロードしたファイル名称をユニーク名称に変更する。
     * ここで変更した名称は、「posts.guid」、「postmeta.meta_value」に設定される。
     * 
     * @param string $name ファイル名称
     */
    function sanitize_file_name($name)
    {
      // ファイル名が変換対象の拡張子かどうかを確認する
      if (!$this->isRenameExtension($name)) {
        return $name;
      }
      return $this->createUniqueName($name);
    }

    /**
     * メディア情報登録時の「post_title」、「post_name」を変更したファイル名称と同じにする
     * @param array $data An array of sanitized attachment post data.
     * @param array $postarr An array of unsanitized attachment post data.
     */
    function wp_insert_attachment_data($data, $postarr)
    {
      // ファイル名称を取得する
      $name = $postarr['file'] ? basename($postarr['file']) : '';
      if (empty($name)) {
        return $data;
      }
      // ファイル名が変換対象の拡張子かどうかを確認する
      if (!$this->isRenameExtension($name)) {
        return $data;
      }

      // 作成ずみのユニーク名称を使用して更新する
      $data['post_title'] = $name;
      $data['post_name'] = $name;

      return $data;
    }

    /**
     * ファイル名称が変換対象かどうかを確認する
     * @param string $name ファイル名称
     */
    private function isRenameExtension($name)
    {
      // 拡張子を取得して、処理対象拡張子と同じかどうかを確認する
      $extension = pathinfo($name, PATHINFO_EXTENSION);
      if (in_array($extension, self::FILE_EXTENSION, true)) {
        return true;
      }
      return false;
    }

    /**
     * ユニーク名称を作成する。
     * @return string ユニーク名称
     */
    private function createUniqueName($name)
    {
      // 名称から拡張子を取得する
      $extension = pathinfo($name, PATHINFO_EXTENSION);
      // ユニーク名称を作成する
      return md5(uniqid(rand(), true)) . '.' . $extension;
    }

  }
  new UniqueRenameImageFileUpload();
}
?>
