class Event < ActiveRecord::Base
  belongs_to :venue
  has_many   :minutes

  validates_presence_of :agenda, :venue_id, :date, :created_at
  validates_size_of     :title, :within => 1..150
  validates_length_of   :agenda, :maximum => 4086, :message => "too long. Max length is 4KB"
  validates_length_of   :description, :maximum => 10240, :message => "too long. Max length is 10KB"
end
