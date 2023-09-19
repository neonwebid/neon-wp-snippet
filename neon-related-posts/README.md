# Neon Related Posts

menampilkan related posts berdasarkan Taxonomy: ``category``, ``post_tag``, dan taxonomy custom lainnya. bisa digunakan di widget, bisa digunakan didalam Post Content.

## Cara Pakai
- copy code (neon-related-posts.php)[neon-related-posts]
- paste di dalam file ``functions.php``
- letakkan shortcode ``[neon_related_post]`` di dalam konten
- atau gunakan code php ``NeonRelatedPosts::get($post_id)`` untuk ditampilkan diberbagai tempat.

### parameter shortcode
``[neon_related_post posts_item=3 by='category' show_thumbnail=true show_post_meta=true]``

- **posts_item** : jumlah post yang ingin ditampilkan
- **by** : taxonomy yang digunakan secara default menggunakan ``category``. dapat diganti dengan ``post_tag`` atau taxonomy custom yang telah dibuat.
- **show_thumbnail**: untuk menampilkan post thumbnail
- **show_post_meta**: untuk menampilkan tanggal posting

### parameter ``NeonRelatedPosts::get( $post_id, $args = array() )``

- **posts_item** : jumlah post yang ingin ditampilkan
- **by** : taxonomy yang digunakan secara default menggunakan ``category``. dapat diganti dengan ``post_tag`` atau taxonomy custom yang telah dibuat.
- **show_thumbnail**: untuk menampilkan post thumbnail
- **show_post_meta**: untuk menampilkan tanggal posting

contoh penggunaan:
```php

$post_id = 10;

NeonRelatedPosts::get( $post_id, [
  'posts_item' => 3,
  'by' => 'category',
  'show_thumbnail' => false,
  'show_post_meta' => false
]);


```
