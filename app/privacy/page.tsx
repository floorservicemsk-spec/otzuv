export default function PrivacyPage() {
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4">
      <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">
          Политика конфиденциальности
        </h1>
        
        <div className="prose prose-gray max-w-none space-y-6">
          <section>
            <h2 className="text-2xl font-semibold text-gray-900 mb-4">
              1. Общие положения
            </h2>
            <p className="text-gray-700">
              Настоящая Политика конфиденциальности определяет порядок обработки и защиты 
              персональных данных пользователей при использовании сервиса активации гарантийных 
              талонов на сайте i-laminat.ru.
            </p>
          </section>
          
          <section>
            <h2 className="text-2xl font-semibold text-gray-900 mb-4">
              2. Собираемые данные
            </h2>
            <p className="text-gray-700 mb-2">
              При активации гарантийного талона мы собираем следующую информацию:
            </p>
            <ul className="list-disc pl-6 text-gray-700 space-y-2">
              <li>Номер телефона или номер договора</li>
              <li>Информация о дополнительных работах (по желанию)</li>
              <li>Оценки качества обслуживания (по желанию)</li>
              <li>Выбранные товары для бронирования скидок</li>
              <li>IP-адрес и технические данные браузера</li>
            </ul>
          </section>
          
          <section>
            <h2 className="text-2xl font-semibold text-gray-900 mb-4">
              3. Цели обработки данных
            </h2>
            <ul className="list-disc pl-6 text-gray-700 space-y-2">
              <li>Активация гарантийного талона</li>
              <li>Резервирование персональных скидок</li>
              <li>Улучшение качества обслуживания</li>
              <li>Связь с клиентом по вопросам гарантии</li>
              <li>Статистический анализ и улучшение сервиса</li>
            </ul>
          </section>
          
          <section>
            <h2 className="text-2xl font-semibold text-gray-900 mb-4">
              4. Защита данных
            </h2>
            <p className="text-gray-700">
              Мы применяем современные технические и организационные меры для защиты ваших 
              персональных данных от несанкционированного доступа, изменения, раскрытия или уничтожения.
            </p>
          </section>
          
          <section>
            <h2 className="text-2xl font-semibold text-gray-900 mb-4">
              5. Ваши права
            </h2>
            <p className="text-gray-700 mb-2">
              Вы имеете право:
            </p>
            <ul className="list-disc pl-6 text-gray-700 space-y-2">
              <li>Получать информацию о хранящихся персональных данных</li>
              <li>Требовать исправления неточных данных</li>
              <li>Требовать удаления данных</li>
              <li>Отозвать согласие на обработку данных</li>
            </ul>
          </section>
          
          <section>
            <h2 className="text-2xl font-semibold text-gray-900 mb-4">
              6. Контакты
            </h2>
            <p className="text-gray-700">
              По вопросам обработки персональных данных обращайтесь: <br />
              Email: privacy@i-laminat.ru <br />
              Телефон: 8 (800) 123-45-67
            </p>
          </section>
          
          <section>
            <p className="text-sm text-gray-600 mt-8">
              Последнее обновление: {new Date().toLocaleDateString('ru-RU')}
            </p>
          </section>
        </div>
        
        <div className="mt-8 pt-6 border-t border-gray-200">
          <a
            href="/garantia"
            className="text-primary-600 hover:text-primary-700 font-medium hover:underline"
          >
            ← Вернуться к форме активации
          </a>
        </div>
      </div>
    </div>
  );
}
