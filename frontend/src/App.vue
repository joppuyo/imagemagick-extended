<template>
  <div class="wrap">


    <div class="ime-app">

      <h1>Imagemagick Extended</h1>

      <div class="ime-grid">

        <div class="ime-grid-item">

          <Panel title="General settings">
            <form>
              <label for="filter" class="imex-label">Resampling filter</label>

                <select v-model="options.filter" id="filter" class="imex-select">
                  <option v-for="filter in this.context.resampling_filters"
                          v-bind:value="filter"
                  >
                    {{ getFilterName(filter) }}
                  </option>
                </select>

            </form>
          </Panel>
          <Panel title="JPEG settings">
            <form>

              <div class="imex-control">

                <label class="imex-label" for="subsampling">Chroma subsampling</label>

                <select v-model="options.chroma_subsampling" id="subsampling" class="imex-select imex-mb-20">
                  <option value="420">4:2:0 *</option>
                  <option value="422">4:2:2</option>
                  <option value="444">4:4:4</option>
                </select>

              </div>


              <div class="imex-control">

                <div class="imex-checkbox imex-mb-20">
                  <label class="imex-checkbox-label">
                    <input type="checkbox" class="imex-checkbox-input">
                    <span class="imex-checkbox-custom-checkbox"></span>
                    <span class="imex-checkbox-custom-label">
                    Set custom quality
                    </span>
                  </label>
                </div>

              </div>


              <label for="quality" class="imex-label">Quality</label>

              <div class="ime-quality">
                <div class="ime-quality-slider">
                  <input type="range" id="quality" name="quality"
                         min="0" max="100" class="imex-range">

                </div>
                <div class="ime-quality-number">
                  <input type="number">
                </div>
              </div>

            </form>
          </Panel>

        </div>

        <div class="ime-grid-item">

          <div class="ime-subgrid">

            <div class="ime-grid-item">

        <Panel title="Original">
          <div class="imex-test-image">
            <img v-bind:src="context.test_images[0]">
          </div>
        </Panel>

            </div>

            <div class="ime-grid-item">

        <Panel title="Resampled">
          <div class="imex-preview-image">
            <img v-if="this.base64Image" v-bind:src="base64Image">
          </div>
        </Panel>

            </div>

          </div>

        </div>

      </div>

      <form>
        <div>
          <button type="submit" v-on:click.prevent="save">Save</button>
        </div>
      </form>

    </div>
  </div>
</template>

<script>
import axios from 'axios';
import qs from 'qs';
import Panel from "./Panel.vue";

export default {
  components: {
    Panel,
  },
  props: ['context'],
  data: function () {
    return {
      translations: window.imex_translations,
      loading: true,
      options: {},
      base64Image: null,
      image: 0,
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
        'FILTER_LANCZOS': 'Lanchoz *',
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

    // XXX

    const slider = document.querySelector(".imex-range")
    const min = slider.min
    const max = slider.max
    const value = slider.value

    slider.style.background = `linear-gradient(to right, #007AFF 0%, #007AFF ${(value - min) / (max - min) * 100}%, #D8D8D8 ${(value - min) / (max - min) * 100}%, #D8D8D8 100%)`

    slider.oninput = function () {
      this.style.background = `linear-gradient(to right, #007AFF 0%, #007AFF ${(this.value - this.min) / (this.max - this.min) * 100}%, #D8D8D8 ${(this.value - this.min) / (this.max - this.min) * 100}%, #D8D8D8 100%)`
    };

    // XXX
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
    options: {
      handler: function (newValue, oldValue) {
        console.log('hhhh')
        this.updatePreview();
      }, deep: true
    }
  },
}
</script>