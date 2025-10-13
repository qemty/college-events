from graphviz import Digraph

# Создаем объект Digraph с вертикальной ориентацией и настройками для формата A4
dot = Digraph(comment='Mental Map by Roles', graph_attr={'rankdir': 'TB', 'splines': 'ortho', 'size': '7.5,10.5!', 'ratio': 'fill'})

# Основной узел - Авторизация
dot.node('auth', 'Авторизация', color='black', style='filled', fillcolor='lightgreen', shape='box')

# Подграф для роли "Студент"
with dot.subgraph(name='cluster_student') as c:
    c.attr(label='Личный кабинет студента', style='rounded', fillcolor='lightyellow', nodesep='0.1', ranksep='0.2')
    c.node('s1', 'Профиль студента', shape='box')
    c.node('s2', 'Дашборд студента\n(предстоящие мероприятия)', shape='box')
    c.node('s3', 'Список доступных мероприятий', shape='box')
    c.node('s4', 'Детали мероприятия', shape='box')
    c.node('s5', 'Регистрация на мероприятие', shape='box')
    c.node('s6', 'Отмена записи', shape='box')
    c.node('s7', 'История посещений', shape='box')
    c.node('s8', 'Отметка посещения\nс помощью QR-кода', shape='box')

# Подграф для роли "Куратор"
with dot.subgraph(name='cluster_curator') as c:
    c.attr(label='Личный кабинет куратора', style='rounded', fillcolor='lightpink', nodesep='0.1', ranksep='0.2')
    c.node('c1', 'Профиль куратора', shape='box')
    c.node('c2', 'Дашборд куратора\n(предстоящие мероприятия)', shape='box')
    c.node('c3', 'Список всех мероприятий', shape='box')
    c.node('c4', 'Управление посещаемостью', shape='box')
    c.node('c5', 'Отчеты по группам', shape='box')
    c.node('c6', 'Отчеты по студентам', shape='box')
    c.node('c7', 'Рейтинг студентов', shape='box')
    c.node('c8', 'Экспорт данных\n(PDF, Excel, Word)', shape='box')
    c.node('c9', 'Справка для куратора', shape='box')

# Подграф для роли "Администратор"
with dot.subgraph(name='cluster_admin') as c:
    c.attr(label='Административная панель', style='rounded', fillcolor='lightpink', nodesep='0.1', ranksep='0.2')
    c.node('a1', 'Профиль администратора', shape='box')
    c.node('a2', 'Дашборд администратора\n(общая статистика)', shape='box')
    c.node('a3', 'Создание мероприятия', shape='box')
    c.node('a4', 'Редактирование мероприятия', shape='box')
    c.node('a5', 'Список всех мероприятий', shape='box')
    c.node('a6', 'Управление пользователями', shape='box')
    c.node('a7', 'Управление группами', shape='box')
    c.node('a8', 'Отчеты и аналитика', shape='box')
    c.node('a9', 'Экспорт данных\n(PDF, Excel, Word)', shape='box')
    c.node('a10', 'Справка для администратора', shape='box')
    c.node('a11', 'Системные настройки', shape='box')

# Связи между авторизацией и ролями
dot.edge('auth', 'cluster_student')
dot.edge('auth', 'cluster_curator')
dot.edge('auth', 'cluster_admin')

# Внутренние связи для "Студент"
dot.edge('s3', 's4')
dot.edge('s5', 's6')
dot.edge('s7', 's8')

# Внутренние связи для "Куратор"
dot.edge('c3', 'c4')
dot.edge('c4', 'c5')
dot.edge('c4', 'c6')
dot.edge('c5', 'c8')
dot.edge('c6', 'c8')

# Внутренние связи для "Администратор"
dot.edge('a3', 'a5')
dot.edge('a4', 'a5')
dot.edge('a6', 'a7')
dot.edge('a8', 'a9')

# Сохранение и отображение графа
dot.render('mental_map_by_roles', format='png', view=True)