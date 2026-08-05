<script setup>
import { computed, onMounted, ref } from 'vue'
import { dashboardApi } from '@/api/dashboard'
import { formatCurrency } from '@/lib/format'
import PageHeader from '@/components/ui/PageHeader.vue'
import StatCard from '@/components/ui/StatCard.vue'
import AppCard from '@/components/ui/AppCard.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'
import PieChart from '@/components/charts/PieChart.vue'
import BarChart from '@/components/charts/BarChart.vue'

const summary = ref(null)
const registrations = ref([])
const incomeBySport = ref([])
const loading = ref(true)
const loadError = ref('')

const registrationTypeLabels = { day: 'Day', week: 'Week', month: 'Month', year: 'Year' }

const registrationSegments = computed(() =>
  registrations.value.map((item) => ({
    label: registrationTypeLabels[item.type] || item.type,
    value: item.count,
    percentage: item.percentage,
  })),
)

const incomeBars = computed(() =>
  incomeBySport.value.map((item) => ({ label: item.sport, value: item.income })),
)

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const [summaryRes, registrationsRes, incomeRes] = await Promise.all([
      dashboardApi.summary(),
      dashboardApi.registrationsChart(),
      dashboardApi.incomeBySportChart(),
    ])
    summary.value = summaryRes.data
    registrations.value = registrationsRes.data
    incomeBySport.value = incomeRes.data
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'Failed to load dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <PageHeader title="Dashboard" :subtitle="summary ? summary.gym_name : ''" />

    <LoadingBlock v-if="loading" />
    <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ loadError }}
    </p>

    <template v-else-if="summary">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard label="Total Clients" :value="summary.total_clients" />
        <StatCard label="Total Coaches" :value="summary.total_coaches" />
        <StatCard label="Coach Salaries" :value="formatCurrency(summary.coach_salaries)" />
        <StatCard label="Total Income" :value="formatCurrency(summary.total_income)" tone="positive" />
        <StatCard label="Total Expenses" :value="formatCurrency(summary.total_expenses)" tone="negative" />
        <StatCard
          label="Net Profit"
          :value="formatCurrency(summary.net_profit)"
          :tone="summary.net_profit >= 0 ? 'positive' : 'negative'"
        />
      </div>

      <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <AppCard title="Registrations by type">
          <PieChart :data="registrationSegments" />
        </AppCard>
        <AppCard title="Income by sport">
          <BarChart :data="incomeBars" value-prefix="" />
        </AppCard>
      </div>
    </template>
  </div>
</template>
