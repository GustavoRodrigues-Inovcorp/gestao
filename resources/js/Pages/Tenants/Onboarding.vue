<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import { Check, Building2, Shield, ArrowRight } from 'lucide-vue-next'

const page = usePage()
const tenantAtual = computed(() => page.props.tenant_atual)
const tenantUsers = computed(() => page.props.tenant_users ?? [])

const step = ref(1)
const totalSteps = 3

// Branding form (uses Empresa endpoint)
const empresaForm = useForm({ nome: tenantAtual.value?.nome ?? '', logotipo: null })
const logoPreview = ref(null)

const inviteForm = useForm({ })
const inviteDraft = ref('')
const inviteEmails = ref([])

function addInviteEmails() {
  const emails = inviteDraft.value
    .split(/[;,\s]+/)
    .map(email => email.trim().toLowerCase())
    .filter(Boolean)

  for (const email of emails) {
    if (!inviteEmails.value.includes(email)) inviteEmails.value.push(email)
  }

  inviteDraft.value = ''
}

function removeInviteEmail(email) {
  inviteEmails.value = inviteEmails.value.filter(item => item !== email)
}

function onLogoChange(e) {
  const f = e.target.files?.[0]
  empresaForm.logotipo = f
  if (f) {
    const reader = new FileReader()
    reader.onload = () => { logoPreview.value = reader.result }
    reader.readAsDataURL(f)
  } else {
    logoPreview.value = null
  }
}

function saveBranding() {
  empresaForm.post('/configuracoes/empresa', {
    forceFormData: true,
    onSuccess: () => {
      // Marca automaticamente o item da empresa como feito
      const item = checklist.value.find(i => i.id === 1)
      if (item) item.done = true
      step.value = 2
    },
  })
}

function inviteUsers() {
  if (inviteEmails.value.length === 0) return
  inviteForm
    .transform(() => ({ emails: inviteEmails.value }))
    .post(`/tenants/${tenantAtual.value.id}/onboarding/invite`, {
      preserveScroll: true,
      onSuccess: () => {
        const item = checklist.value.find(i => i.id === 3)
        if (item) item.done = true
        inviteEmails.value = []
        router.reload({ only: ['tenant_users'] })
      },
    })
}

// Nota: as permissões iniciais foram removidas do onboarding.
// A gestão de permissões continua disponível em Acessos -> Permissões.

// Checklist (simple links to sections)
const checklist = ref([
  { id: 1, label: 'Configurar dados da empresa', done: false, required: true },
  { id: 2, label: 'Definir grupos de permissões', done: false, required: false },
  { id: 3, label: 'Convidar utilizadores', done: false, required: true },
])

const completedChecklist = computed(() => checklist.value.filter(item => item.done).length)
const checklistProgress = computed(() => Math.round((completedChecklist.value / checklist.value.length) * 100))
const requiredChecklistDone = computed(() => checklist.value.filter(item => item.required).every(item => item.done))
const canFinish = computed(() => requiredChecklistDone.value)

function finish() {
  if (!canFinish.value) return
  router.post(`/tenants/${tenantAtual.value.id}/onboarding/complete`, {
    checklist: checklist.value,
  }, { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <template #header>
      <h1 class="text-sm font-semibold">Onboarding — Configuração inicial</h1>
    </template>

    <div class="max-w-3xl space-y-6">
      <div class="rounded-lg border bg-card p-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold">Passo {{ step }} de {{ totalSteps }}</h2>
            <p class="text-sm text-muted-foreground">Configurações iniciais para o workspace</p>
          </div>
          <Badge variant="outline">{{ tenantAtual?.nome ?? 'Workspace' }}</Badge>
        </div>

        <div v-if="step === 3" class="mt-5 space-y-2">
          <div class="flex items-center justify-between text-sm">
            <span class="text-muted-foreground">Progresso da checklist</span>
            <span class="font-medium">{{ completedChecklist }}/{{ checklist.length }} concluído</span>
          </div>
          <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
            <div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${checklistProgress}%` }"></div>
          </div>
          <p class="text-xs text-muted-foreground">
            Para concluir o onboarding tens de marcar os itens obrigatórios.
          </p>
        </div>

        <div class="mt-6">

          <!-- Step 1: Branding -->
          <div v-if="step === 1">
            <Label>Nome da Empresa</Label>
            <Input v-model="empresaForm.nome" />

            <div class="mt-3">
              <Label>Logotipo</Label>
              <Input type="file" accept="image/*" @change="onLogoChange" />
              <div v-if="logoPreview" class="mt-3">
                <img :src="logoPreview" alt="Pré-visualização" class="h-20 object-contain rounded-md" />
              </div>
            </div>

            <div class="mt-4 flex gap-2">
              <Button @click="saveBranding" :disabled="empresaForm.processing">Guardar e continuar</Button>
            </div>
          </div>

          <!-- Step 2: Invite users -->
          <div v-if="step === 2">
            <div class="mt-6 rounded-lg border bg-background p-4 space-y-3">
              <div>
                <Label>Convidar utilizadores</Label>
                <p class="text-xs text-muted-foreground">Escreve um ou mais emails e carrega em Enter ou no botão para adicionar.</p>
              </div>
              <div class="rounded-md border bg-card p-3 space-y-3">
                <div class="flex flex-wrap gap-2">
                  <Badge v-for="email in inviteEmails" :key="email" variant="secondary" class="flex items-center gap-2 px-2 py-1">
                    <span>{{ email }}</span>
                    <button type="button" class="text-xs opacity-70 hover:opacity-100" @click="removeInviteEmail(email)">×</button>
                  </Badge>
                  <span v-if="inviteEmails.length === 0" class="text-xs text-muted-foreground">Ainda não adicionaste emails.</span>
                </div>
                <div class="flex gap-2">
                  <Input
                    v-model="inviteDraft"
                    placeholder="ana@exemplo.com"
                    @keydown.enter.prevent="addInviteEmails"
                  />
                  <Button variant="outline" type="button" @click="addInviteEmails">Adicionar</Button>
                </div>
                <div class="flex items-end">
                  <Button class="w-full" @click="inviteUsers" :disabled="inviteForm.processing || inviteEmails.length === 0">Convidar utilizadores</Button>
                </div>
              </div>

              <div class="space-y-2">
                <Label>Utilizadores já no workspace</Label>
                <div class="rounded-md border bg-card p-3">
                  <div v-if="tenantUsers.length" class="space-y-2">
                    <div v-for="user in tenantUsers" :key="user.id" class="flex items-center justify-between gap-3 text-sm">
                      <div class="min-w-0">
                        <p class="font-medium truncate">{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                      </div>
                      <Badge variant="secondary">{{ user.role ?? 'member' }}</Badge>
                    </div>
                  </div>
                  <p v-else class="text-xs text-muted-foreground">Ainda não há utilizadores associados a este workspace.</p>
                </div>
              </div>
            </div>

            <div class="mt-4 flex gap-2">
              <Button variant="outline" @click="step = 1">Voltar</Button>
              <Button @click="step = 3">Continuar</Button>
            </div>
          </div>

          <!-- Step 3: Checklist -->
          <div v-if="step === 3">
            <Label>Checklist</Label>
            <div class="mt-3 space-y-2">
              <div v-for="item in checklist" :key="item.id" class="flex items-center gap-3">
                <input type="checkbox" v-model="item.done" />
                <button type="button" class="text-sm flex-1 text-left hover:underline" @click="router.visit(item.href)">
                  {{ item.label }}
                </button>
                <Badge variant="secondary" class="text-[10px] uppercase tracking-wide">
                  {{ item.required ? 'Obrigatório' : 'Opcional' }}
                </Badge>
              </div>
            </div>
            <div class="mt-4 flex gap-2">
              <Button variant="outline" @click="step = 2">Voltar</Button>
              <Button @click="finish" :disabled="!canFinish">Concluir onboarding</Button>
            </div>
            <p v-if="!canFinish" class="mt-2 text-xs text-muted-foreground">
              Marca todos os itens obrigatórios para concluir.
            </p>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>
