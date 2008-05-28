class Person < ActiveRecord::Base
  has_many :articles, :foreign_key => "author_id"
  has_many :minutes, :foreign_key => "author_id"
  has_many :pages, :foreign_key => "author_id"


  def self.authenticate(username, password)
    password = OpenSSL::Digest::SHA1.new(password).hexdigest
    user     = Person.find(:first, :select => [:password], :conditions => ["username = ?", username])
    return (user and user.password == password) ? true : false
  end

end
