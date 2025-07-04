<x-filament::page>
    <h2 class="text-xl font-bold mb-4">My Draft Posts</h2>

    @forelse ($this->posts as $post)
        <div class="p-4 border rounded mb-2">
            <h3 class="text-lg font-semibold">{{ $post->title }}</h3>
            <p class="text-sm text-gray-500">
                Category: {{ $post->category->name ?? 'None' }} |
                Tags: {{ $post->tags->pluck('name')->join(', ') }}
            </p>
        </div>
    @empty
        <p>No draft posts found.</p>
    @endforelse
</x-filament::page>
