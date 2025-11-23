
export function getMenu(user) {
  const items = [
    { title: 'Dashboard', icon: 'pi pi-home', route: '/' },
    { title: 'Jadwal Travel', icon: 'pi pi-calendar', route: '/jadwal-travel' },
  ]

  if (user && user.role === 'admin') {
    items.push({ title: 'Management User', icon: 'pi pi-users', route: '/users' })
  }

  if (user && user.role === 'passenger') {
    items.push({ title: 'My Tickets', icon: 'pi pi-ticket', route: '/my-ticket' })
  }

  return [
    {
      group: 'Main',
      items,
    },
  ]
}

export default getMenu;
