<x-app-layout>

    <x-page.header title="content_decryption::content_decryption.index" create="content_decryption::content_decryption.create"></x-page.header>
    <x-main>
        <div class="table-responsive">
        <table id="content_decryption_table" class="table table-striped table-hover ">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Process Pid</th>
                <th>Server Name</th>
<!--                <th>Decryption Key</th>-->
                <th>RTMP Url</th>
                <th>Url</th>
                <th>Created at</th>
                <th>Updated at</th>
                <th class="actions">Actions</th>
            </tr>
            </thead>
        </table>
        </div>
        @push('scripts')
        <script type="text/javascript" src="{{ asset('js/scripts/mpd_streams/content_decryption.js') }}?{{config('app.version')}}"></script>
        @endpush
    </x-main>
</x-app-layout>
