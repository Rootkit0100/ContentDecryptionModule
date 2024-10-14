 <x-main-form>
            <form
                action="{{ $formUrl }}"
                method="POST"
                autocomplete="off"
            >
                @method($formMethod)
                @csrf

                <div class="row">

                    <div class="col-md-12 callout callout-info">
                        <h4>How It Works</h4>
                        <p>The system decrypts the provided MPD URL and streams it via RTMP to the specified server.</p>

                        <h4>Setup Instructions</h4>
                        <p>Fill in the required fields. The RTMP URL is generated automatically in the following format:
                            <code>rtmp://{server_domain || server_ip}/line/{stream_name}</code></p>
                        <p>Navigate to <strong>Manage MPD Streams</strong> and click <strong>Decrypt And Start Pushing</strong>.</p>
                        <p>Ensure that you whitelist the IPs for both publishing and playback.</p>
                    </div>


                    <div class="col-md-2 form-group">
                        <label>Name</label>
                        <input value="{{@$streamMpd->name}}" name="name" required class="form-control input">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="server_id">Server</label>

                        <select required name="server_id" class="required form-control search-streams-select2">
                            @foreach($servers as $key => $server)
                               <option {{(@$streamMpd->server_id == $server->id) ? 'selected' : ''}} value="{{$server->id}}"> {{$server->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="server_id">Rtmp Push Server</label>
                        <select required name="rtmp_server_id" class="required form-control search-streams-select2">
                            @foreach($rtmpServers as $key => $server)
                            <option {{(@$streamMpd->rtmp_server_id == $server->id) ? 'selected' : ''}} value="{{$server->id}}"> {{$server->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label>Mpd Url</label>
                        <textarea rows="5" required  name="url" class="form-control  input">{{@$streamMpd->url}}</textarea>
                    </div>
                    <div class="col-md-8 form-group">
                        <label>Decryption Key</label>
                        <input required value="{{@$streamMpd->decryption_key}}" name="decryption_key" class="form-control input">
                    </div>
                </div>
                <x-form-btn><i class="fas fa-save"></i> {{ __('common.buttons.save') }}</x-form-btn>
            </form>



        </x-main-form>
        @push('scripts')

        @endpush

