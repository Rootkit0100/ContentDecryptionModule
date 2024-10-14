<x-app-layout>

    <x-page.header title="content_decryption::content_decryption.create"></x-page.header>
    <x-main>
        <x-main-form>
            @include('content_decryption::pages.content_decryption.form', ['formUrl' => route('content_decryption.store'), 'formMethod' => 'POST'])
        </x-main-form>
    </x-main>
</x-app-layout>
