<style>
    pre {
        position: relative;
        max-height: 400px;
        padding: 20px 15px;
        background: #fff;
        word-break: break-all;
        word-wrap: break-word;
        overflow-y: scroll;
        white-space: pre;
        font-size: 14px;
        color: #666666;
        border-left: 3px solid #ec6952;
    }
</style>

<div 
    id="rr-vue-app" 
    resource-url="<?= $resource_url ?>" 
    servers="<?= htmlspecialchars(json_encode($servers)) ?>"
    >
    <!-- Servers Table -->
    <div class="col-group">
        <div class="col w-16">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Site ID</th>
                        <th>Server ID</th>
                        <th>Name</th>
                        <th>Short Code</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in servers">
                        <td>
                            <button :disabled="fetchingForServer(s.server_id)" class="btn" @click="getListingsData(s.server_id)">
                                <span v-if="!fetchingForServer(s.server_id)">Fetch</span>
                                <span v-else>Fetching...</span>
                            </button>
                        </td>
                        <td>{{s.site_id}}</td>
                        <td>{{s.server_id}}</td>
                        <td>{{s.name}}</td>
                        <td>{{s.short_code}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-group" style="margin-top: 10px">
        <div class="col w-8">
            <input type="text" v-model="search" placeholder="Search fields...">
        </div>
    </div>

    <!-- Response View -->
    <div class="col-group" style="margin-top:20px">
        <div class="col w-16">
            <pre>{{listingsDataRaw}}</pre>
        </div>
    </div>
</div>