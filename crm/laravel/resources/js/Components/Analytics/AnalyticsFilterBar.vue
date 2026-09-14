<template>
  <div class="flex flex-wrap items-center gap-3">
    <!-- Period Selector -->
    <div class="flex items-center bg-slate-900 border border-slate-800 rounded-xl p-1 text-xs">
      <button 
        v-for="p in periodOptions" 
        :key="p.value"
        @click="selectPeriod(p.value)"
        :class="[
          'px-3 py-1.5 rounded-lg font-medium transition-all',
          modelValue.period === p.value 
            ? 'bg-blue-600 text-white shadow-md' 
            : 'text-slate-400 hover:text-slate-200'
        ]"
      >
        {{ p.label }}
      </button>
    </div>

    <!-- Custom Date Range -->
    <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-1 text-xs">
      <input 
        type="date" 
        :value="modelValue.dateFrom"
        @change="updateFilter('dateFrom', $event.target.value)"
        class="bg-transparent text-slate-200 focus:outline-none" 
      />
      <span class="text-slate-600">—</span>
      <input 
        type="date" 
        :value="modelValue.dateTo"
        @change="updateFilter('dateTo', $event.target.value)"
        class="bg-transparent text-slate-200 focus:outline-none" 
      />
    </div>

    <!-- Channel Select -->
    <select 
      :value="modelValue.channelId" 
      @change="updateFilter('channelId', $event.target.value)"
      class="bg-slate-900 border border-slate-800 text-slate-300 text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-blue-500"
    >
      <option value="">All Channels</option>
      <option v-for="channel in channels" :key="channel.id" :value="channel.id">
        {{ channel.name }}
      </option>
    </select>

    <!-- Campaign Select -->
    <select 
      :value="modelValue.campaignId" 
      @change="updateFilter('campaignId', $event.target.value)"
      class="bg-slate-900 border border-slate-800 text-slate-300 text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-blue-500"
    >
      <option value="">All Campaigns</option>
      <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
        {{ campaign.name }}
      </option>
    </select>

    <!-- Reset Button -->
    <button 
      @click="$emit('reset')" 
      class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl text-xs font-medium transition-colors"
    >
      Reset
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Object, required: true },
  channels: { type: Array, default: () => [] },
  campaigns: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:modelValue', 'reset']);

const periodOptions = [
  { label: 'Today', value: 'today' },
  { label: '7 Days', value: '7d' },
  { label: '30 Days', value: '30d' },
];

function updateFilter(key, value) {
  emit('update:modelValue', { ...props.modelValue, [key]: value });
}

function selectPeriod(period) {
  emit('update:modelValue', { 
    ...props.modelValue, 
    period, 
    dateFrom: '', 
    dateTo: '' 
  });
}
</script>