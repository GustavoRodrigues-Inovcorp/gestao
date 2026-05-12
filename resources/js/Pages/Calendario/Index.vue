<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import ptLocale from '@fullcalendar/core/locales/pt'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogFooter,
} from '@/Components/ui/dialog'
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import { Trash2 } from 'lucide-vue-next'

const props = defineProps({
    tipos: Array,
    acoes: Array,
    entidades: Array,
    utilizadores: Array,
})

// Filtros
const filtroUser = ref(null)
const filtroEntidade = ref(null)

// Dialog
const showDialog = ref(false)
const isEdit = ref(false)
const eventoAtual = ref(null)

const form = ref(defaultForm())

function defaultForm() {
    return {
        id: null,
        titulo: '',
        inicio: '',
        fim: '',
        duracao: null,
        entidade_id: null,
        tipo_id: null,
        acao_id: null,
        descricao: '',
        partilha: false,
        conhecimento: false,
        estado: 'pendente',
    }
}

// Configuração do FullCalendar
const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locale: ptLocale,
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    editable: true,
    selectable: true,
    events: fetchEventos,
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
    height: 'auto',
}))

async function fetchEventos(fetchInfo, successCallback, failureCallback) {
    const params = new URLSearchParams()
    if (filtroUser.value) params.append('user_id', filtroUser.value)
    if (filtroEntidade.value) params.append('entidade_id', filtroEntidade.value)

    try {
        const res = await fetch(`/calendario/eventos?${params}`)
        const data = await res.json()
        successCallback(data)
    } catch (e) {
        failureCallback(e)
    }
}

const calendarRef = ref(null)

function refetchEventos() {
    calendarRef.value?.getApi().refetchEvents()
}

function handleDateSelect(info) {
    isEdit.value = false
    form.value = defaultForm()
    form.value.inicio = info.startStr.slice(0, 16)
    form.value.fim = info.endStr.slice(0, 16)
    showDialog.value = true
}

function handleEventClick(info) {
    isEdit.value = true
    const e = info.event
    const ep = e.extendedProps
    form.value = {
        id: e.id,
        titulo: e.title,
        inicio: e.startStr.slice(0, 16),
        fim: e.endStr?.slice(0, 16) ?? '',
        duracao: ep.duracao,
        entidade_id: ep.entidade_id,
        tipo_id: ep.tipo_id,
        acao_id: ep.acao_id,
        descricao: ep.descricao ?? '',
        partilha: ep.partilha,
        conhecimento: ep.conhecimento,
        estado: ep.estado,
    }
    eventoAtual.value = info.event
    showDialog.value = true
}

async function handleEventDrop(info) {
    const e = info.event
    await fetch(`/calendario/eventos/${e.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            titulo: e.title,
            inicio: e.startStr,
            fim: e.endStr,
            duracao: e.extendedProps.duracao,
            entidade_id: e.extendedProps.entidade_id,
            tipo_id: e.extendedProps.tipo_id,
            acao_id: e.extendedProps.acao_id,
            descricao: e.extendedProps.descricao,
            partilha: e.extendedProps.partilha,
            conhecimento: e.extendedProps.conhecimento,
            estado: e.extendedProps.estado,
        }),
    })
}

async function handleEventResize(info) {
    await handleEventDrop(info)
}

async function submitForm() {
    const method = isEdit.value ? 'PUT' : 'POST'
    const url = isEdit.value
        ? `/calendario/eventos/${form.value.id}`
        : '/calendario/eventos'

    await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(form.value),
    })

    showDialog.value = false
    refetchEventos()
}

async function deleteEvento() {
    if (!confirm('Eliminar este evento?')) return
    await fetch(`/calendario/eventos/${form.value.id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    showDialog.value = false
    refetchEventos()
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Calendário</h1>
        </template>

        <div class="space-y-4">
            <!-- Filtros -->
            <div class="flex items-center gap-4 p-4 rounded-lg border bg-card">
                <span class="text-sm font-medium text-muted-foreground">Filtrar por:</span>
                <div class="w-48">
                    <Select v-model="filtroUser" @update:modelValue="refetchEventos">
                        <SelectTrigger class="h-8 text-xs">
                            <SelectValue placeholder="Utilizador..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">Todos</SelectItem>
                            <SelectItem v-for="u in utilizadores" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="w-48">
                    <Select v-model="filtroEntidade" @update:modelValue="refetchEventos">
                        <SelectTrigger class="h-8 text-xs">
                            <SelectValue placeholder="Entidade..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">Todas</SelectItem>
                            <SelectItem v-for="e in entidades" :key="e.id" :value="e.id">
                                {{ e.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Calendário -->
            <div class="rounded-lg border bg-card p-4">
                <FullCalendar ref="calendarRef" :options="calendarOptions" />
            </div>
        </div>

        <!-- Dialog Evento -->
        <Dialog v-model:open="showDialog">
            <DialogContent class="max-w-lg max-h-[85vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ isEdit ? 'Editar Evento' : 'Novo Evento' }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Título *</Label>
                        <Input v-model="form.titulo" />
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="space-y-1">
                            <Label>Início *</Label>
                            <Input v-model="form.inicio" type="datetime-local" />
                        </div>
                        <div class="space-y-1">
                            <Label>Fim</Label>
                            <Input v-model="form.fim" type="datetime-local" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Duração (minutos)</Label>
                        <Input v-model="form.duracao" type="number" min="1" placeholder="ex: 60" />
                    </div>
                    <div class="space-y-1">
                        <Label>Entidade</Label>
                        <Select v-model="form.entidade_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar entidade..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="null">Nenhuma</SelectItem>
                                <SelectItem v-for="e in entidades" :key="e.id" :value="e.id">
                                    {{ e.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="space-y-1">
                            <Label>Tipo</Label>
                            <Select v-model="form.tipo_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Tipo..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">Nenhum</SelectItem>
                                    <SelectItem v-for="t in tipos" :key="t.id" :value="t.id">
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: t.cor }"></div>
                                            {{ t.nome }}
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Ação</Label>
                            <Select v-model="form.acao_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Ação..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">Nenhuma</SelectItem>
                                    <SelectItem v-for="a in acoes" :key="a.id" :value="a.id">
                                        {{ a.nome }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Descrição</Label>
                        <Textarea v-model="form.descricao" rows="3" />
                    </div>
                    <div class="space-y-1">
                        <Label>Estado</Label>
                        <Select v-model="form.estado">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pendente">Pendente</SelectItem>
                                <SelectItem value="concluido">Concluído</SelectItem>
                                <SelectItem value="cancelado">Cancelado</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.partilha" id="partilha" class="rounded" />
                            <Label for="partilha">Partilhar com equipa</Label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.conhecimento" id="conhecimento" class="rounded" />
                            <Label for="conhecimento">Para conhecimento</Label>
                        </div>
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <Button v-if="isEdit" variant="destructive" size="sm" @click="deleteEvento" class="gap-1 mr-auto">
                        <Trash2 class="w-4 h-4" /> Eliminar
                    </Button>
                    <Button variant="outline" @click="showDialog = false">Cancelar</Button>
                    <Button @click="submitForm">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>