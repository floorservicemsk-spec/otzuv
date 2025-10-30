export default function PrivacyPolicyPage() {
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4">
      <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">
          Политика конфиденциальности
        </h1>
        
        <div className="prose prose-gray max-w-none">
          <section className="mb-8">
            <h2 className="text-xl font-semibold mb-4">1. Общие положения</h2>
            <p className="text-gray-700 mb-4">
              Настоящая Политика конфиденциальности определяет порядок обработки и защиты 
              персональных данных пользователей веб-сайта при активации гарантийного талона.
            </p>
          </section>

          <section className="mb-8">
            <h2 className="text-xl font-semibold mb-4">2. Собираемые данные</h2>
            <p className="text-gray-700 mb-4">
              При заполнении формы активации гарантии мы собираем следующие данные:
            </p>
            <ul className="list-disc pl-6 text-gray-700 mb-4">
              <li>Номер телефона или номер договора</li>
              <li>Информация о дополнительных работах</li>
              <li>Оценки качества сервиса (продавцы, доставка, монтаж)</li>
              <li>Выбранные скидки</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-xl font-semibold mb-4">3. Цели обработки</h2>
            <p className="text-gray-700 mb-4">
              Персональные данные обрабатываются в следующих целях:
            </p>
            <ul className="list-disc pl-6 text-gray-700 mb-4">
              <li>Активация гарантийного талона</li>
              <li>Обработка обратной связи о качестве сервиса</li>
              <li>Бронирование скидок на сопутствующие товары и услуги</li>
              <li>Улучшение качества обслуживания</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-xl font-semibold mb-4">4. Защита данных</h2>
            <p className="text-gray-700 mb-4">
              Мы принимаем все необходимые меры для защиты персональных данных от 
              несанкционированного доступа, изменения, раскрытия или уничтожения.
            </p>
          </section>

          <section className="mb-8">
            <h2 className="text-xl font-semibold mb-4">5. Права пользователя</h2>
            <p className="text-gray-700 mb-4">
              Вы имеете право:
            </p>
            <ul className="list-disc pl-6 text-gray-700 mb-4">
              <li>Получать информацию о ваших персональных данных</li>
              <li>Требовать исправления неточных данных</li>
              <li>Требовать удаления ваших персональных данных</li>
              <li>Отозвать согласие на обработку персональных данных</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-xl font-semibold mb-4">6. Контакты</h2>
            <p className="text-gray-700 mb-4">
              По вопросам обработки персональных данных обращайтесь по адресу: 
              <a href="mailto:privacy@i-laminat.ru" className="text-primary-600 hover:underline ml-1">
                privacy@i-laminat.ru
              </a>
            </p>
          </section>

          <section className="mb-8">
            <p className="text-sm text-gray-500">
              Последнее обновление: {new Date().toLocaleDateString('ru-RU')}
            </p>
          </section>
        </div>
      </div>
    </div>
  )
}
