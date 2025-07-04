<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;

   class ReactionController extends Controller
{
   public function store(Post $post, string $type)
{

      $user = auth()->user();

   $reaction = Reaction::firstOrNew([
    'post_id' => $post->id,
    'user_id' => $user->id,
    ]);

   if ($reaction->exists && $reaction->type === $type) {
          $reaction->delete();
                } else {
          $reaction->type = $type;
          $reaction->save();
 }

    return back();
}
}
