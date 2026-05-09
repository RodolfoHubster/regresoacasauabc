/* eventos.js – Carga y renderiza eventos (preparado para PHP/MySQL) */

// TODO: Reemplazar este array con una llamada fetch() a la API PHP
// Ejemplo: fetch('api/eventos.php').then(r => r.json()).then(renderEvents)

const eventosEjemplo = [
  {
    id: 1,
    nombre: 'Regresa a Casa – Tijuana 2025',
    fecha: '15 de Agosto, 2025',
    hora: '10:00 AM',
    campus: 'Campus Tijuana – Auditorio Central',
    descripcion: 'Reencuentro de egresados de todas las generaciones en el campus Tijuana.',
    imagen: 'https://picsum.photos/seed/uabc1/600/300',
    disponible: true
  },
  {
    id: 2,
    nombre: 'Regresa a Casa – Mexicali 2025',
    fecha: '22 de Octubre, 2025',
    hora: '9:00 AM',
    campus: 'Campus Mexicali – Centro de Convenciones',
    descripcion: 'Celebración anual de egresados en campus Mexicali.',
    imagen: 'https://picsum.photos/seed/uabc2/600/300',
    disponible: true
  },
  {
    id: 3,
    nombre: 'Regresa a Casa – Ensenada 2025',
    fecha: 'Diciembre 2025',
    hora: 'Por confirmar',
    campus: 'Campus Ensenada',
    descripcion: 'Próximo evento de egresados en el campus Ensenada.',
    imagen: 'https://picsum.photos/seed/uabc3/600/300',
    disponible: false
  }
];

// Función auxiliar – disponible para uso futuro con datos dinámicos
function renderEventos(eventos) {
  const grid = document.getElementById('events-grid');
  if (!grid || !eventos) return;
  // TODO: implementar render dinámico desde API
  console.log('Eventos cargados:', eventos.length);
}

document.addEventListener('DOMContentLoaded', () => {
  renderEventos(eventosEjemplo);
});
