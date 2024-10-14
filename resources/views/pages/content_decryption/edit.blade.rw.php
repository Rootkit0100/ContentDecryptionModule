<x-app-layout>

    <x-page.header title="content_decryption::content_decryption.edit"></x-page.header>
    <x-main>
        <x-main-form>
            @include('content_decryption::pages.content_decryption.form', ['formUrl' => route('content_decryption.update', $streamMpd->id), 'formMethod' => 'PUT'])
        </x-main-form>
    </x-main>
</x-app-layout>
