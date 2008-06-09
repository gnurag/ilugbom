class Person < ActiveRecord::Base
  has_many :articles, :foreign_key => "author_id"
  has_many :minutes, :foreign_key => "author_id"
  has_many :pages, :foreign_key => "author_id"


  def self.authenticate(username, password)
    password = OpenSSL::Digest::SHA1.new(password).hexdigest
    user     = Person.find(:first, :conditions => ["username = ? AND deleted = 0", username])
    return (user and user.password == password) ? user : false
  end

  def hash_user_password(password)
    self.password = OpenSSL::Digest::SHA1.new(password).hexdigest
  end

  ## Get pipe separated value for a Person object
  def get_psv
    psv = []
    psv[0] = self.id.to_s
    psv[1] = self.username
    psv[2] = self.fullname
    psv[3] = self.nickname
    psv[4] = self.irc_nick
    psv[5] = self.email
    psv[6] = self.webpage
    psv[7] = self.flickr_username
    psv[8] = self.yahooim_username
    psv[9] = self.gtalk_username
    psv[10] = self.visible
    return "#{psv[0]}|#{psv[1]}|#{psv[2]}|#{psv[3]}|#{psv[4]}|#{psv[5]}|#{psv[6]}|#{psv[7]}|#{psv[8]}|#{psv[9]}|#{psv[10]}"
  end


end
