new Vue({
    data: function() {
        return {
            http: {
                listings: {
                    active: false,
                    id: null
                }
            },
            servers: [],
            listingsData: [],
            errors: {
                listings: null
            },
            url: '',
            search: ''
        }
    },
    mounted: function () {
        this.url = this.$el.attributes['resource-url'].value;
        this.servers = JSON.parse(this.$el.attributes['servers'].value || []);

        if(this.servers && this.servers.length) {
            var defaultS = null;

            // Set offsets to 0 for all servers
            this.servers = this.servers.map(function (s) {
                s.offset = 0;
                if(s.is_default) {
                    defaultS = s.server_id;
                }

                return s;
            });

            if(defaultS) {
                this.getListingsData(defaultS);
            } else {
                this.getListingsData(this.servers[0].server_id);
            }
        }
    },
    methods: {
        getListingsData: function (serverId) {
            if(this.http.listings.active)
                return;

            var self = this;
            var data = {
                "filter": "server_id eq " + serverId,
            };
            var server = this.getServer(serverId);

            this.http.listings.active = true;
            this.http.listings.id = serverId;
            this.errors.listings = null;
            this.listingsData = null;

            data['skip'] = server.offset;

            axios.get(this.url, {
                params: data
            }).then(function (res) {
                self.listingsData = res.data[0];
                self.http.listings.active = false;
                self.http.listings.id = null;

                self.incrementServerOffset(serverId);
            }).catch(function (res) {
                self.http.listings.id = null;
                self.http.listings.active = false;
            });
        },
        getServer: function (id) {
            var t = this.servers.filter(function (s) {
                return s.server_id === id;
            });

            if(t.length) {
                return t[0];
            } else {
                return null;
            }
        },
        incrementServerOffset: function (serverId) {
            let index = -1;

            for(var i = 0; i < this.servers.length; i++) {
                if(this.servers[i].server_id === serverId) {
                    index = i;
                    break;
                }
            }

            if(index > -1) {
                this.servers[index].offset++;
            }
        },
        fetchingForServer: function (serverId) {
            return this.http.listings.active && this.http.listings.id === serverId;
        }
    },
    computed: {
        listingsDataRaw: function () {
            var result = '';
            var self = this;

            if(this.listingsData) {
                if(this.search) {
                    let r = null;
                    let newListingsData = {};
                    let keys = [];
                    
                    // avoid polluting source of truth
                    r = JSON.parse(JSON.stringify(this.listingsData));

                    // search matching keys
                    Object.keys(r).forEach(function (k) {
                        if(k.toLowerCase().includes(self.search)) {
                            keys.push(k);
                        }
                    });

                    // rebuild object
                    keys.map(function (k) {
                        newListingsData[k] = r[k];
                    });

                    result = JSON.stringify(newListingsData, null, 2);
                } else {
                    result = JSON.stringify(this.listingsData, null, 2);
                }
            }

            return result;
        }
    }
}).$mount('#rr-vue-app');