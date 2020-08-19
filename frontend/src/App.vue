<template>
    <form>
        <label>
            <span style="display: block">Chroma subsampling</span>
            <select v-model="options.chroma_subsampling">
                <option value="420">4:2:0</option>
                <option value="422">4:2:2</option>
                <option value="444">4:4:4</option>
            </select>
        </label>
        <label>
            <span style="display: block">Resampling filter</span>
            <select v-model="options.filter">
                <option v-for="filter in this.context.resampling_filters"
                        v-bind:value="filter"
                >
                    {{getFilterName(filter)}}
                </option>
            </select>
        </label>
        <div>
            <button type="submit" v-on:click.prevent="save">Save</button>
            <div class="imex-preview-image">
                <img v-if="this.base64Image" v-bind:src="base64Image"></img>
            </div>
        </div>
    </form>
</template>

<script>
    import axios from 'axios';
    import qs from 'qs';
    export default {
        props: ['context'],
        data: function () {
            return {
                translations: window.imex_translations,
                loading: true,
                options: {},
                base64Image: null,
                filter_names: {
                    'FILTER_POINT': 'Point',
                    'FILTER_BOX': 'Box',
                    'FILTER_TRIANGLE': 'Triangle',
                    'FILTER_HERMITE': 'Hermite',
                    'FILTER_HANNING': 'Hanning',
                    'FILTER_HAMMING': 'Hamming',
                    'FILTER_BLACKMAN': 'Blackman',
                    'FILTER_GAUSSIAN': 'Gaussian',
                    'FILTER_QUADRATIC': 'Quadratic',
                    'FILTER_CUBIC': 'Cubic',
                    'FILTER_CATROM': 'Catmull-Rom',
                    'FILTER_MITCHELL': 'Mitchell',
                    'FILTER_LANCZOS': 'Lanchoz',
                    'FILTER_BESSEL': 'Bessel',
                    'FILTER_SINC': 'Sinc',
                }
            }
        },
        mounted() {
            let data = {
                'action': 'imex_get_options',
            };
            axios.post(ajaxurl, qs.stringify(data)).then(response => {
                this.options = response.data;
                this.loading = false;
            }).catch(error => {
                console.error(error);
            })
        },
        methods: {
            getFilterName: function (filter) {
                return this.filter_names[filter];
            },
            save: function () {

                let data = {
                    'action': 'imex_save_options',
                    'imex_options': JSON.stringify(this.options),
                };

                axios.post(ajaxurl, qs.stringify(data))
                    .then(response => {
                        this.$toasted.show(this.translations.options_saved);
                    }).catch((error) => {
                    console.error(error)
                })
            },
            updatePreview: function () {
                let data = {
                    'action': 'imex_preview',
                    'imex_options': JSON.stringify(this.options),
                };

                axios.post(ajaxurl, qs.stringify(data), {
                    responseType: 'blob',
                })
                    .then(response => {
                        //this.base64Image = window.URL.createObjectURL(response.data);
                        let reader = new FileReader();
                        reader.readAsDataURL(response.data);
                        reader.onloadend = () => {
                            this.base64Image = reader.result;
                        };

                    }).catch((error) => {
                    console.error(error)
                })
            },
        },
        watch: {
            options: {handler: function (newValue, oldValue) {
                console.log('hhhh')
                this.updatePreview();
            }, deep: true}
        },
    }
</script>