new Vue({
    data: function() {
        return {
            http: {
                listings: false
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
            this.servers = this.servers.map(function (s) {
                s.offset = 0;

                return s;
            });
            
            this.getListingsData(this.servers[0].server_id);
        }
    },
    methods: {
        getListingsData: function (serverId) {
            if(this.http.listings)
                return;

            var self = this;
            var data = {
                "filter": "server_id eq " + serverId,
            };
            var server = this.getServer(serverId);

            this.http.listings = true;
            this.errors.listings = null;
            this.listingsData = null;

            data['skip'] = server.offset;

            axios.get(this.url, {
                params: data
            }).then(function (res) {
                self.listingsData = res.data[0];
                self.http.listings = false;

                self.incrementServerOffset(serverId);
            }).catch(function (res) {
                self.http.listings = false;
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
        incrementServerOffset: function (id) {
            let index = -1;

            for(var i = 0; i < this.servers.length; i++) {
                if(this.servers[i].server_id === id) {
                    index = i;
                    break;
                }
            }

            if(index > -1) {
                this.servers[index].offset++;
            }
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